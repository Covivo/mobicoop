<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\UserAddress;
use App\Form\UserCreateForm;

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
        return $this->render('user/detail.html.twig', [
            'user' => $userManager->getUser($id)
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
            'hydra' => $userManager->getUsers()
        ]);
    }
    
    /**
     * Create a user.
     *
     * @Route("/user/create", name="user_create")
     *
     */
    public function userCreate(UserManager $userManager, Request $request)
    {
        $user = new User();
        
        $form = $this->createForm(UserCreateForm::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->createUser($user)) return $this->redirectToRoute('users');
        }
        
        return $this->render('user/create.html.twig', [
                'form' => $form->createView(),
        ]);
    }
}
