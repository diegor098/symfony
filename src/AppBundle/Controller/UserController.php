<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Users;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class UserController extends Controller {

    /**
     * @Route("/new", name="user_new")
     */
    public function newAction(Request $request){
        $helpers= $this->get(Helpers::class);

        $json = $request->get('json',null);

        $data = array(
            'status' => 'error',
            'code' => 400,
            'msg' => 'User not created'
        );

        if($json !=null){
            $createdAt = new \Datetime("now");
            $role = "User";

            $params = json_decode($json);

            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message= "This email not valid";
            $validate_email= $this->get("validator")->validate($email,$emailConstraint);
            
            if($surname != null && $name != null && $email != null && count($validate_email) == 0 && $password != null){
                $user = new Users();
                $user->setCreatedAt($createdAt);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                $em= $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository("BackendBundle:Users")->findBy(array("email"=>$email));

                if(count($isset_user)==0){
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status' => 'success',
                        'code' => 200,
                        'msg' => 'User created !!!',
                        'user' => $user
                    );

                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'msg' => 'User duplicated !!!'
                    );
                }
                
            }
        
        }   
        
        return $helpers->json($data);
    }

    /**
     * @Route("/edit", name="user_edit")
     */
    public function editAction(Request $request){
        $helpers= $this->get(Helpers::class);
        $jwt_auth=$this->get(JwtAuth::class);

        $token = $request->get('authorization',null);
        $checkToken= $jwt_auth->checkToken($token);

        if($checkToken){
                $em= $this->getDoctrine()->getManager();

                $identity= $jwt_auth->checkToken($token,true);

                $user = $em->getRepository("BackendBundle:Users")->findOneBy(array("id"=>$identity->sub));

                        $json = $request->get('json',null);

                        $data = array(
                            'status' => 'error',
                            'code' => 400,
                            'msg' => 'User not updated'
                        );

                        if($json !=null){
                            $createdAt = new \Datetime("now");
                            $role = "User";

                            $params = json_decode($json);

                            $name = (isset($params->name)) ? $params->name : null;
                            $surname = (isset($params->surname)) ? $params->surname : null;
                            $email = (isset($params->email)) ? $params->email : null;
                            $password = (isset($params->password)) ? $params->password : null;
                            
                            $emailConstraint = new Assert\Email();
                            $emailConstraint->message= "This email not valid";
                            $validate_email= $this->get("validator")->validate($email,$emailConstraint);
                            
                            if($surname != null && $name != null && $email != null && count($validate_email) == 0 && $password != null){
                               
                                $user->setCreatedAt($createdAt);
                                $user->setRole($role);
                                $user->setEmail($email);
                                $user->setName($name);
                                $user->setSurname($surname);

                                
                                $isset_user = $em->getRepository("BackendBundle:Users")->findBy(array("email"=>$email));

                                if(count($isset_user)==0 || $identity->email == $email){
                                    $em->persist($user);
                                    $em->flush();

                                    $data = array(
                                        'status' => 'success',
                                        'code' => 200,
                                        'msg' => 'User updated !!!',
                                        'user' => $user
                                    );

                                }else{
                                    $data = array(
                                        'status' => 'error',
                                        'code' => 400,
                                        'msg' => 'User duplicated !!!'
                                    );
                                }
                                
                            }
                        
                        }   
            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'data' => 'Authorization not valid!!!'
                );
            }
        return $helpers->json($data);
    }


}