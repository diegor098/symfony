<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\Helpers;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/login", name="default_login")
     */
    public function loginAction(Request $request){
        $helpers= $this->get(Helpers::class);

        $json = $request->get('json',null);

        $data=array(
            'status' => 'error',
            'error' => 'Send json via post!!'
        );

        if($json !=null){
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message= "This email not valid";
            $validate_email= $this->get("validator")->validate($email,$emailConstraint);

            if($email != null && count($validate_email) == 0 && $password != null){
                $jwt_auth=$this->get(JwtAuth::class);

                if($getHash == null || $getHash == false){
                $signup= $jwt_auth->signup($email,$password);
                }else{
                $signup= $jwt_auth->signup($email,$password,true);  
                }
                
                return $this->json($signup);
            }else{
                $data=array(
                    'status' => 'success',
                    'data' => 'Email or Password InCorrect'
                );
            }
        }

        return $helpers->json($data);
    }

    /**
     * @Route("/pruebas", name="default_pruebas")
     */
    public function pruebasAction(Request $request)
    {
        $helpers= $this->get(Helpers::class);
        $jwt_auth= $this->get(JwtAuth::class);
        $token= $request->get("authorization",null);

        if($token && $jwt_auth->checkToken($token)==true){
            
        $em= $this->getDoctrine()->getManager();
        $users=$em->getRepository("BackendBundle:Users")->FindAll();

        return $helpers->json(array("status" => "success","users"=>$users));
        }else{
            return $helpers->json(array("status" => "success","data"=>'Authorization not valid!!'));
        }
    }
}
