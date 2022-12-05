<?php

namespace App\Incentive\Service;

use App\DataProvider\Entity\MobConnect\MobConnectAuthProvider;
use App\DataProvider\Ressource\MobConnectAuthParams;
use App\Incentive\Entity\MobConnectAuth;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * Manager the user authentication for the service MobConnect.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectAuthManager
{
    private const SERVICE_NAME = 'mobConnect';

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

    private function __getJWTToken(): string
    {
        $userAuth = $this->_user->getMobConnectAuth();

        $this->_authProvider = new MobConnectAuthProvider(new MobConnectAuthParams($this->__getMobConnectSsoParams()), $this->_user);

        $response = $this->_authProvider->getJWTToken($userAuth->getAuthorizationCode(), $userAuth->getRefreshToken());

        $userAuth->setAccessToken($response->getAccessToken());
        $userAuth->setRefreshToken($response->getRefreshToken());

        $this->_em->flush();

        return $userAuth->getAccessToken();
    }

    private function __getMobConnectSsoParams(): ?array
    {
        foreach ($this->_ssoServices as $key => $service) {
            if (preg_match('/'.self::SERVICE_NAME.'/', $key)) {
                return $service;
            }
        }

        throw new \LogicException(MobConnectMessages::MOB_CONFIG_UNAVAILABLE, Response::HTTP_BAD_REQUEST);
    }

    private function __isAccessAuthorized(): bool
    {
        // L'utilisateur n'a pas de compte associé
        return
            !is_null($this->_user->getMobConnectAuth())
            && !empty($this->_user->getMobConnectAuth()->getAuthorizationCode())
        ;
    }

    public function createAuth(string $authorizationCode)
    {
        $mobConnectAuth = new MobConnectAuth($this->_user, $authorizationCode);

        $this->_em->persist($mobConnectAuth);
        $this->_em->flush();
    }

    public function getAuthenticatedUser(): User
    {
        if (!$this->__isAccessAuthorized()) {
            throw new BadRequestHttpException(MobConnectMessages::MOB_CONNECTION_ERROR);
        }

        $this->__getJWTToken();

        return $this->_user;
    }
}
