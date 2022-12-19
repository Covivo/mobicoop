<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * MobConnect API provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectApiProvider extends MobConnectProvider
{
    private const SERVICE_NAME = 'mobConnect';

    private const ROUTE_SUBSCRIPTIONS = '/v1/maas/subscriptions';
    private const ROUTE_PATCH_SUBSCRIPTIONS = '/v1/subscriptions/{SUBSCRIPTION_ID}';
    private const ROUTE_SUBSCRIPTIONS_VERIFY = '/v1/subscriptions/{SUBSCRIPTION_ID}/verify';

    private const SHORT_DISTANCE_LABEL = 'Court';
    private const LONG_DISTANCE_LABEL = 'Long';

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var MobConnectApiParams
     */
    private $_apiParams;

    /**
     * @var array
     */
    private $_ssoServices;

    public function __construct(EntityManagerInterface $em, MobConnectApiParams $params, User $user, array $ssoServices)
    {
        $this->_em = $em;
        $this->_apiParams = $params;

        $this->_apiUri = $this->_apiParams->getApiUri();
        $this->_user = $user;
        $this->_ssoServices = $ssoServices;
    }

    private function __getSubscriptionId(bool $shortDistance = false): string
    {
        return $shortDistance ? $this->_apiParams->getShortDistanceSubscriptionId() : $this->_apiParams->getLongDistanceSubscriptionId();
    }

    private function __getToken(): string
    {
        $mobConnectAuth = $this->_user->getMobConnectAuth();

        if (is_null($mobConnectAuth)) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_MISSING);
        }

        $now = new \DateTime('now');

        if ($now >= $mobConnectAuth->getRefreshTokenExpiresDate()) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_EXPIRED);
        }

        return $now >= $mobConnectAuth->getAccessTokenExpiresDate()
            ? $this->__refreshToken() : $mobConnectAuth->getAccessToken();
    }

    private function __postSubscription(string $incentiveId, bool $isShortDistance = false, string $phoneNumber = null)
    {
        $data = [
            'incentiveId' => $incentiveId,
            'consent' => true,
            'Type de trajet' => true === $isShortDistance ? [self::SHORT_DISTANCE_LABEL] : [self::LONG_DISTANCE_LABEL],
            'Numéro de permis de conduire' => $this->_user->getDrivingLicenseNumber(),
        ];

        if (false === $isShortDistance) {
            $data['Numéro de téléphone'] = self::LONG_DISTANCE_LABEL;
        }

        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS);

        return $this->_getResponse(
            $this->_dataProvider->postCollection($data, $this->_buildHeaders($this->__getToken()))
        );
    }

    private function __refreshToken()
    {
        if (!array_key_exists(self::SERVICE_NAME, $this->_ssoServices)) {
            throw new \LogicException(str_replace('{SERVICE_NAME}', self::SERVICE_NAME, MobConnectMessages::MOB_CONFIG_UNAVAILABLE));
        }

        $service = $this->_ssoServices[self::SERVICE_NAME];

        $provider = new OpenIdSsoProvider(
            self::SERVICE_NAME,
            '',
            $service['baseUri'],
            $service['clientId'],
            $service['clientSecret'],
            '',
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

    public function postSubscriptionForShortDistance()
    {
        return new MobConnectSubscriptionResponse($this->__postSubscription($this->_apiParams->getShortDistanceSubscriptionId(), true));
    }

    public function postSubscriptionForLongDistance()
    {
        return new MobConnectSubscriptionResponse($this->__postSubscription($this->_apiParams->getLongDistanceSubscriptionId(), false, $this->_user->getTelephone()));
    }

    /**
     * Updates a user subscription with a carpool proof.
     *
     * @param string            $subscriptionId  The ID of the subscription that needs to be updated
     * @param string            $rpcJourneyId    The RPC ID of the journey
     * @param bool              $isShortDistance Specifies whether the trip is a short distance trip
     * @param DateTimeInterface $costSharingDate The date of payment for the trip
     */
    public function patchUserSubscription(
        string $subscriptionId,
        ?string $rpcJourneyId = null,
        bool $isShortDistance = false,
        ?\DateTimeInterface $costSharingDate = null
    ): MobConnectSubscriptionResponse {
        $data = [];
        // TODO: Vérifier le fonctionnement de la requête
        if (true === $isShortDistance) {
            $data['Identifiant du trajet'] = $rpcJourneyId;
        } else {
            $data['Date de partage des frais'] = $costSharingDate->format('d/m/Y');
        }

        $this->_createDataProvider(self::ROUTE_PATCH_SUBSCRIPTIONS, $subscriptionId);

        return new MobConnectSubscriptionResponse(
            $this->_getResponse($this->_dataProvider->patchItem($data, $this->_buildHeaders($this->__getToken())))
        );
    }

    public function verifyUserSubscription(string $subscriptionId): MobConnectSubscriptionVerifyResponse
    {
        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS_VERIFY, $subscriptionId);

        return new MobConnectSubscriptionVerifyResponse(
            $this->_getResponse(
                $this->_dataProvider->postCollection(null, $this->_buildHeaders($this->__getToken()))
            )
        );
    }
}
