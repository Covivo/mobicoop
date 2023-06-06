<?php

namespace App\User\Admin\Controller;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DeleteUsers
{
    /**
     * @var Request
     */
    private $_request;

    /**
     * @var UserManager
     */
    private $_userManager;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository, UserManager $userManager)
    {
        $this->_request = $requestStack->getCurrentRequest();

        $this->_userRepository = $userRepository;

        $this->_userManager = $userManager;
    }

    public function __invoke()
    {
        /**
         * @var User[]
         */
        $users = $this->_userRepository->findFilteredUsers($this->_request->query->all());

        foreach ($users as $user) {
            $this->_userManager->deleteUser($user);
        }
    }
}
