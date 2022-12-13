<?php

namespace App\Incentive\Service;

use App\DataProvider\Entity\MobConnect\MobConnectAuthProvider;
use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use App\Incentive\Entity\MobConnectAuth;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Manager the user authentication for the service MobConnect.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectAuthManager
{
    private const SERVICE_NAME = 'mobConnect';
    private const BASE_SITE_URI = 'http://localhost:8081';

    /**
     * @var MobConnectAuthProvider
     */
    private $_authProvider;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var array
     */
    private $_ssoServices;

    /**
     * @var User
     */
    private $_user;

    public function __construct(EntityManagerInterface $em, Security $security, array $ssoServices)
    {
        $this->_em = $em;
        $this->_user = $security->getUser();
        $this->_ssoServices = $ssoServices;
    }

    private function __refreshToken()
    {
        if (!array_key_exists(self::SERVICE_NAME, $this->_ssoServices)) {
            throw new \LogicException(str_replace('{SERVICE_NAME}', self::SERVICE_NAME, MobConnectMessages::MOB_CONFIG_UNAVAILABLE));
        }

        $service = $this->_ssoServices[self::SERVICE_NAME];

        $provider = new OpenIdSsoProvider(
            self::SERVICE_NAME,
            self::BASE_SITE_URI,
            $service['baseUri'],
            $service['clientId'],
            $service['clientSecret'],
            self::BASE_SITE_URI.'/'.$service['returnUrl'],
            $service['autoCreateAccount'],
            $service['logOutRedirectUri'] = '',
            $service['codeVerifier'] = null
        );

        $mobConnectAuth = $this->_user->getMobConnectAuth();

        $tokens = $provider->getRefreshToken($mobConnectAuth->getRefreshToken());

        $mobConnectAuth->updateTokens($tokens);

        $this->_em->flush();

        return $mobConnectAuth->getAccessToken();
    }

    public function createAuth(User $user, SsoUser $ssoUser)
    {
        $mobConnectAuth = new MobConnectAuth($user, $ssoUser);

        $this->_em->persist($mobConnectAuth);
        $this->_em->flush();
    }

    public function getToken(): string
    {
        $mobConnectAuth = $this->_user->getMobConnectAuth();

        if (is_null($mobConnectAuth)) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_MISSING);
        }

        $now = new \DateTime('now');

        if ($now >= $mobConnectAuth->getRefreshTokenExpiresDate()) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_EXPIRED);
        }

        if ($now >= $mobConnectAuth->getAccessTokenExpiresDate()) {
            $token = $mobConnectAuth->getAccessToken();
        } else {
            $token = $this->__refreshToken();
        }

        return $token;
    }
}
