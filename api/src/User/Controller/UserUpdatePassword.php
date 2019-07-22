<?php
 /**
	* Created by PhpStorm.
	* User: vagrant
	* Date: 7/22/19
	* Time: 3:06 PM
	*/
 
 namespace App\User\Controller;
 
 
 use App\User\Entity\User;
 use App\User\Service\UserManager;

 class UserUpdatePassword
 {
	private $userManager;
 
	/**
	 * UserUpdatePassword constructor.
	 * @param UserManager $userManager
	 */
	public function __construct(UserManager $userManager)
	{
	 $this->userManager = $userManager;
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
	 $data = $this->userManager->updateUser($data);
	 return $data;
	}
 }