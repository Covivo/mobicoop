<?php

namespace App\User\Admin\Controller;

use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DeleteUsers
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var UserManager
     */
    private $_userManager;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, UserManager $userManager)
    {
        $this->_em = $em;
        $this->_request = $requestStack->getCurrentRequest();

        $this->_userManager = $userManager;
    }

    public function __invoke()
    {
        $users = $this->_em->getRepository(User::class)->findBy($this->_request->query->all());

        foreach ($users as $user) {
            $this->_userManager->deleteUser($user);
        }
    }
}
