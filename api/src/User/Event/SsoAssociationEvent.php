<?php

namespace App\User\Event;

use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class SsoAssociationEvent extends Event
{
    public const NAME = 'user_sso_association_success';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var SsoUser
     */
    protected $ssoUser;

    public function __construct(User $user, SsoUser $ssoUser)
    {
        $this->user = $user;
        $this->ssoUser = $ssoUser;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSsoUser()
    {
        return $this->ssoUser;
    }
}
