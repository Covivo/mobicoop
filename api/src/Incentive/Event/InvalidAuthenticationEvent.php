<?php

namespace App\Incentive\Event;

use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class InvalidAuthenticationEvent extends Event
{
    public const NAME = 'mobconnect_invalid_authentication';

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user)
    {
        $this->_user = $user;
    }

    public function getUser(): User
    {
        return $this->_user;
    }
}
