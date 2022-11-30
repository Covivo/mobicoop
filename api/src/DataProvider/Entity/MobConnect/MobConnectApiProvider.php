<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionVerifyResponse;
use App\DataProvider\Ressource\MobConnectApiParams;
use App\User\Entity\User;

/**
 * MobConnect API provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectApiProvider extends MobConnectProvider
{
    private const ROUTE_SUBSCRIPTIONS = '/v1/maas/subscriptions';
    private const ROUTE_PATCH_SUBSCRIPTIONS = '/v1/subscriptions/{SUBSCRIPTION_ID}';
    private const ROUTE_SUBSCRIPTIONS_VERIFY = '/v1/subscriptions/{SUBSCRIPTION_ID}/verify';

    /**
     * @var MobConnectApiParams
     */
    private $_apiParams;

    /**
     * @var string
     */
    private $_JWTToken;

    public function __construct(MobConnectApiParams $params, User $user)
    {
        $this->_apiParams = $params;
        $this->_apiUri = $this->_apiParams->getApiUri();
        $this->_user = $user;
    }

    private function __getSubscriptionId(bool $shortDistance = false): string
    {
        return $shortDistance ? $this->_apiParams->getShortDistanceSubscriptionId() : $this->_apiParams->getLongDistanceSubscriptionId();
    }

    private function __postSubscription(string $incentiveId, bool $short = false)
    {
        $data = [
            'incentiveId' => $incentiveId,
            'consent' => true,
            'Type de trajet' => $short,
            'Numéro de permis de conduire' => $this->_user->getDrivingLicenseNumber(),
        ];

        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS);

        return $this->_getResponse(
            $this->_dataProvider->postCollection($data, $this->_buildHeaders($this->_user->getMobConnectAuth()->getAccessToken()))
        );
    }

    public function postSubscriptionForShortDistance()
    {
        return new MobConnectSubscriptionResponse($this->__postSubscription($this->_apiParams->getShortDistanceSubscriptionId(), true));
    }

    public function postSubscriptionForLongDistance()
    {
        return new MobConnectSubscriptionResponse($this->__postSubscription($this->_apiParams->getLongDistanceSubscriptionId()));
    }

    // Updates a user subscription with a carpool proof
    public function patchUserSubscription(string $subscriptionId, string $rpcJourneyId): MobConnectSubscriptionResponse
    {
        $data = [
            'Identifiant du trajet' => $rpcJourneyId,
        ];

        $this->_createDataProvider(self::ROUTE_PATCH_SUBSCRIPTIONS, $subscriptionId);

        return new MobConnectSubscriptionResponse(
            $this->_getResponse($this->_dataProvider->postCollection($data, $this->_buildHeaders($this->_user->getMobConnectAuth()->getAccessToken())))
        );
    }

    public function verifyUserSubscription(string $subscriptionId): MobConnectSubscriptionVerifyResponse
    {
        $this->_createDataProvider(self::ROUTE_SUBSCRIPTIONS_VERIFY, $subscriptionId);

        return new MobConnectSubscriptionVerifyResponse(
            $this->_getResponse(
                $this->_dataProvider->postCollection(null, $this->_buildHeaders($this->_user->getMobConnectAuth()->getAccessToken()))
            )
        );
    }
}
