<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserManager;

/**
 * Controller class for user related actions.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{
    /**
     * Retrieve a user.
     * 
     * @Route("/user/{id}", name="user", requirements={"id"="\d+"})
     * 
     */
    public function user($id, UserManager $userManager)
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => print_r($userManager->getUser($id),true)
        ]);
    }
    
    /**
     * Retrieve all users.
     *
     * @Route("/users", name="users")
     *
     */
    public function users(UserManager $userManager)
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => print_r($userManager->getUsers(),true)
        ]);
    }
}
