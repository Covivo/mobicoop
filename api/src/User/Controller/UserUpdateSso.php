<?php

namespace App\User\Controller;

use App\TranslatorTrait;
use App\User\Entity\User;
use App\User\Service\SsoManager;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UserUpdateSso
{
    use TranslatorTrait;

    /**
     * @var UserManager
     */
    private $_userManager;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var SsoManager
     */
    private $_ssoManager;

    public function __construct(RequestStack $requestStack, SsoManager $ssoManager, UserManager $userManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_ssoManager = $ssoManager;
        $this->_userManager = $userManager;
    }

    /**
     * This method is invoked when the alert preferences are updated for a user.
     *
     * @return Response
     */
    public function __invoke(User $user): ?User
    {
        $params = json_decode($this->_request->getContent());

        if (!property_exists($params, 'redirectUri')) {
            $params->redirectUri = null;
        }

        if (!property_exists($params, 'eec')) {
            $params->eec = true;
        }

        $ssoUser = $this->_ssoManager->getSsoUserProfile($params->ssoProvider, $params->ssoId, $params->baseSiteUri, $params->redirectUri);

        // if ($ssoUser->getEmail() === $user->getEmail()) {
        return $this->_userManager->updateUserSsoProperties($user, $ssoUser, $params->eec);
        // }
    }
}
