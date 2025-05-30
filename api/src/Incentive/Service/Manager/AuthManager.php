<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Entity\MobConnectAuth;
use App\Incentive\Service\LoggerService;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthManager extends MobConnectManager
{
    /**
     * @var User
     */
    private $_user;

    public function __construct(
        EntityManagerInterface $em,
        InstanceManager $instanceManager,
        LoggerService $loggerService
    ) {
        parent::__construct($em, $instanceManager, $loggerService);
    }

    private function __createAuth(User $user, SsoUser $ssoUser)
    {
        $mobConnectAuth = new MobConnectAuth($user, $ssoUser);

        $this->_user->setMobConnectAuth($mobConnectAuth);

        $this->_em->persist($mobConnectAuth);
        $this->_em->flush();
    }

    private function __updateAuth(SsoUser $ssoUser)
    {
        $mobConnectAuth = $this->_user->getMobConnectAuth();

        $mobConnectAuth->setAccessToken($ssoUser->getAccessToken());
        $mobConnectAuth->setAccessTokenExpiresDate($ssoUser->getAccessTokenExpiresDuration());
        $mobConnectAuth->setRefreshToken($ssoUser->getRefreshToken());
        $mobConnectAuth->setRefreshTokenExpiresDate($ssoUser->getRefreshTokenExpiresDuration());

        // #8438 - We consider the authentication to be valid again
        $mobConnectAuth->setValidity(true);

        $this->_em->flush();
    }

    public function updateAuth(User $user, SsoUser $ssoUser)
    {
        $this->_user = $user;

        if (is_null($this->_user->getMobConnectAuth())) {
            $this->__createAuth($this->_user, $ssoUser);
            $this->_loggerService->log('The mobConnectAuth entity for user '.$user->getId().' has been created');
        } else {
            $this->__updateAuth($ssoUser);
            $this->_loggerService->log('The mobConnectAuth entity for user '.$user->getId().' has been updated');
        }

        $this->_em->flush();
    }
}
