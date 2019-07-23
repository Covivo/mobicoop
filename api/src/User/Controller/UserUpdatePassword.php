<?php
 /**
	* Created by PhpStorm.
	* User: vagrant
	* Date: 7/22/19
	* Time: 3:06 PM
	*/
 
 namespace App\User\Controller;
 
 
 use App\Right\Service\PermissionManager;
 use App\User\Entity\User;
 use App\User\Repository\UserRepository;
 use App\User\Service\UserManager;
 use Symfony\Component\HttpFoundation\RequestStack;

 class UserUpdatePassword
 {
	/**
	 * @var UserManager $userManager
	 */
	private $userManager;
 
	private $request;
	private $permissionManager;
	private $userRepository;
 
	public function __construct(RequestStack $requestStack, PermissionManager $permissionManager, UserRepository $userRepository, UserManager $userManager)
	{
	 $this->request = $requestStack->getCurrentRequest();
	 $this->permissionManager = $permissionManager;
	 $this->userRepository = $userRepository;
	 $this->userManager= $userManager;
	}
 
	/**
	 * This method is invoked when a new user registers.
	 * It returns the new user created.
	 *
	 * @param User $data
	 * @return User
	 */
	public function __invoke(User $data): User
	{
	 // we check if we limit to a territory
	 $token= $data->getToken();
	 if (!empty($token)) {
		$data = $this->userManager->updateUserPasswordRequest($data);
	 }
	 else{
		$data = $this->userManager->updateUserPasswordConfirm($data);
	 }
	 return $data;
	}
 }