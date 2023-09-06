<?php

namespace App\User\Event;

use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserHomeAddressUpdateEvent extends Event
{
    public const NAME = 'user_home-address_update';

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user)
    {
        $this->setUser($user);
    }

    /**
     * Get the value of _user.
     */
    public function getUser(): User
    {
        return $this->_user;
    }

    /**
     * Set the value of _user.
     */
    public function setUser(User $user): self
    {
        $this->_user = $user;

        return $this;
    }
}
