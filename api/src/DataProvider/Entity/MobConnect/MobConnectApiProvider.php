<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\AuthenticationProvider\AppAuthenticationProvider;
use App\DataProvider\Entity\MobConnect\AuthenticationProvider\UserAuthenticationProvider;
use App\DataProvider\Entity\MobConnect\Converters\ResponseConverter;
use App\DataProvider\Entity\MobConnect\Response\IncentiveResponse;
use App\DataProvider\Entity\MobConnect\Response\IncentivesResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\DataProvider\Interfaces\AuthenticationProviderInterface;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

// Le Role du provider est de proposer des functions
class MobConnectApiProvider extends MobConnectProvider
{
    /**
     * @var EecInstance
     */
    private $_eecInstance;

    /**
     * @var AuthenticationProviderInterface
     */
    private $_appAuthenticationProvider;

    /**
     * @var AuthenticationProviderInterface
     */
    private $_userAuthenticationProvider;

    public function __construct(EecInstance $eecInstance)
    {
        $this->_eecInstance = $eecInstance;

        $this->_build();
    }

    public function postSubscription(string $subscriptionType, User $user): MobConnectSubscriptionResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($user);

        if (!$token) {
            throw new HttpException(Response::HTTP_CONFLICT, MobConnectMessages::TOKEN_MISSING);
        }

        $data = [
            'incentiveId' => Subscription::TYPE_LONG === $subscriptionType ? $this->_eecInstance->getLdKey() : $this->_eecInstance->getSdKey(),
            'consent' => true,
            'Type de trajet' => Subscription::TYPE_LONG === $subscriptionType ? ['Long'] : ['Court'],
            'Numéro de permis de conduire' => $user->getDrivingLicenceNumber(),
            'Numéro de téléphone' => $user->getTelephone(),
        ];

        $this->_createDataProvider(RouteProvider::ROUTE_SUBSCRIPTIONS);

        $response = new MobConnectSubscriptionResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->postCollection($data, $this->_buildHeaders($token))),
            $data
        );

        if ($this->hasRequestErrorReturned($response)) {
            throw new HttpException($response->getCode(), $response->getContent());
        }

        return $response;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function getSubscription($subscription, User $user): MobConnectSubscriptionResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($subscription->getUser());

        if (!$token) {
            return new MobConnectSubscriptionResponse($this->_userAuthenticationProvider->getResponse());
        }

        $this->_createDataProvider(RouteProvider::ROUTE_GET_SUBSCRIPTION, $subscription->getSubscriptionId());

        return new MobConnectSubscriptionResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->getItem([], $this->_buildHeaders($token)))
        );
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function patchSubscription($subscription, array $data): MobConnectSubscriptionResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($subscription->getUser());

        if (!$token) {
            return new MobConnectSubscriptionResponse($this->_userAuthenticationProvider->getResponse());
        }

        $this->_createDataProvider(RouteProvider::ROUTE_PATCH_SUBSCRIPTIONS, $subscription->getSubscriptionId());

        return new MobConnectSubscriptionResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->patchItem($data, $this->_buildHeaders($token))),
            $data
        );
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function verifySubscription($subscription): MobConnectSubscriptionVerifyResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($subscription->getUser());

        if (!$token) {
            return new MobConnectSubscriptionVerifyResponse($this->_userAuthenticationProvider->getResponse());
        }

        $this->_createDataProvider(RouteProvider::ROUTE_SUBSCRIPTIONS_VERIFY, $subscription->getSubscriptionId());

        return new MobConnectSubscriptionVerifyResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->postCollection(null, $this->_buildHeaders($token)))
        );
    }

    public function getSubscriptionTimestamps(string $subscriptionId): MobConnectSubscriptionTimestampsResponse
    {
        $this->_createDataProvider(RouteProvider::ROUTE_SUBSCRIPTIONS_TIMESTAMPS);

        $token = $this->_appAuthenticationProvider->getToken(null);

        if (!$token) {
            return new MobConnectSubscriptionTimeStampsResponse($this->_appAuthenticationProvider->getResponse());
        }

        return new MobConnectSubscriptionTimestampsResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->getItem(['subscriptionId' => $subscriptionId], $this->_buildHeaders($token)))
        );
    }

    public function getIncentives(User $user): ?IncentivesResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($user);

        if (!$token) {
            return new MobConnectSubscriptionVerifyResponse($this->_userAuthenticationProvider->getResponse());
        }

        $this->_createDataProvider(RouteProvider::ROUTE_INCENTIVES);

        return new IncentivesResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->getItem([], $this->_buildHeaders($token)))
        );
    }

    public function getIncentive(string $incentiveId, User $user): ?IncentiveResponse
    {
        $token = $this->_userAuthenticationProvider->getToken($user);

        if (!$token) {
            return new MobConnectSubscriptionVerifyResponse($this->_userAuthenticationProvider->getResponse());
        }

        $this->_createDataProvider(RouteProvider::ROUTE_INCENTIVE, $incentiveId, RouteProvider::INCENTIVE_ID_TAG);

        return new IncentiveResponse(
            ResponseConverter::convertResponseToHttpFondationResponse($this->_dataProvider->getItem([], $this->_buildHeaders($token)))
        );
    }

    public function hasRequestErrorReturned(MobConnectResponse $response): bool
    {
        return in_array($response->getCode(), MobConnectResponse::ERROR_CODES);
    }

    private function _build(): self
    {
        $this->_apiUri = $this->_eecInstance->getProvider()->getApiUri();

        $this->_appAuthenticationProvider = new AppAuthenticationProvider($this->_eecInstance->getProvider());
        $this->_userAuthenticationProvider = new UserAuthenticationProvider($this->_eecInstance->getProvider());

        return $this;
    }
}
