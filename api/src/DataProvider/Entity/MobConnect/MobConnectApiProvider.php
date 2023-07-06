<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * MobConnect API provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectApiProvider extends MobConnectProvider
{
    public const SERVICE_NAME = 'mobConnect';

    private const ROUTE_SUBSCRIPTIONS = '/v1/subscriptions';
    private const ROUTE_PATCH_SUBSCRIPTIONS = '/v1/subscriptions/{SUBSCRIPTION_ID}';
    private const ROUTE_SUBSCRIPTIONS_VERIFY = '/v1/subscriptions/{SUBSCRIPTION_ID}/verify';
    private const ROUTE_SUBSCRIPTIONS_TIMESTAMPS = '/v1/subscriptions/timestamps';

    private const SHORT_DISTANCE_LABEL = 'Court';
    private const LONG_DISTANCE_LABEL = 'Long';

    /**
     * @var null|string
     */
    protected $_appClientID;

    /**
     * @var null|string
     */
    protected $_appClientSecret;

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

    public function __construct(EntityManagerInterface $em, MobConnectApiParams $params, LoggerService $loggerService, User $user, array $ssoServices)
    {
        $this->_em = $em;
        $this->_apiParams = $params;

        $this->_apiUri = $this->_apiParams->getApiUri();
        $this->_loggerService = $loggerService;
        $this->_user = $user;
        $this->_ssoServices = $ssoServices;
    }

    private function __getSubscriptionId(bool $shortDistance = false): string
    {
        return $shortDistance ? $this->_apiParams->getShortDistanceSubscriptionId() : $this->_apiParams->getLongDistanceSubscriptionId();
    }

    private function __getAppToken(): array
    {
        if (!array_key_exists(self::SERVICE_NAME, $this->_ssoServices)) {
            throw new \LogicException(str_replace('{SERVICE_NAME}', self::SERVICE_NAME, MobConnectMessages::MOB_CONFIG_UNAVAILABLE));
        }

        if (is_null($this->_apiParams->getAppClientId()) || is_null($this->_apiParams->getAppClientSecret())) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'There is a misconfiguration');
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
            $service['codeVerifier'] = null,
            $this->_apiParams->getAppClientId(),
            $this->_apiParams->getAppClientSecret()
        );

        return $provider->getAppToken();
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

    public function postSubscription(bool $isLongDistance = true): MobConnectSubscriptionResponse
    {
        $data = [
            'incentiveId' => $isLongDistance ? $this->_apiParams->getLongDistanceSubscriptionId() : $this->_apiParams->getShortDistanceSubscriptionId(),
            'consent' => true,
            'Type de trajet' => true === $isLongDistance ? [self::LONG_DISTANCE_LABEL] : [self::SHORT_DISTANCE_LABEL],
            'Numéro de permis de conduire' => $this->_user->getDrivingLicenceNumber(),
            'Numéro de téléphone' => $this->_user->getTelephone(),
        ];

        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS);

        return new MobConnectSubscriptionResponse(
            $this->_getResponse($this->_dataProvider->postCollection($data, $this->_buildHeaders($this->__getToken())))
        );
    }

    /**
     * Updates a user subscription with a carpool proof.
     *
     * @param string $subscriptionId The ID of the subscription that needs to be updated
     */
    public function patchUserSubscription(string $subscriptionId, array $data): MobConnectSubscriptionResponse
    {
        $this->_loggerService->log('We PATCH the subscription on mobConnect', 'info', true);
        $this->_createDataProvider(self::ROUTE_PATCH_SUBSCRIPTIONS, $subscriptionId);

        return new MobConnectSubscriptionResponse(
            $this->_getResponse($this->_dataProvider->patchItem($data, $this->_buildHeaders($this->__getToken()))),
            $data
        );
    }

    public function getUserSubscriptionTimestamps(string $subscriptionId)
    {
        $this->_loggerService->log('We get the subscription timestamps on mobConnect', 'info', true);
        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS_TIMESTAMPS);

        $appToken = $this->__getAppToken();

        return
            isset($appToken['access_token'])
            ? new MobConnectSubscriptionTimestampsResponse(
                $this->_getResponse($this->_dataProvider->getItem(['subscriptionId' => $subscriptionId], $this->_buildHeaders($appToken['access_token'])))
            )
            : new MobConnectSubscriptionTimeStampsResponse($appToken);
    }

    public function verifyUserSubscription(string $subscriptionId): MobConnectSubscriptionVerifyResponse
    {
        $this->_loggerService->log('We verify the subscription on mobConnect', 'info', true);
        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS_VERIFY, $subscriptionId);

        return new MobConnectSubscriptionVerifyResponse(
            $this->_getResponse(
                $this->_dataProvider->postCollection(null, $this->_buildHeaders($this->__getToken()))
            )
        );
    }
}
