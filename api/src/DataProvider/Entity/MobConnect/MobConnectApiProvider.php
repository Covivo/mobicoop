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

    private const SHORT_DISTANCE_LABEL = 'Court';
    private const LONG_DISTANCE_LABEL = 'Long';

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

    private function __postSubscription(string $incentiveId, bool $isShortDistance = false, string $phoneNumber = null)
    {
        $data = [
            'incentiveId' => $incentiveId,
            'consent' => true,
            'Type de trajet' => true === $isShortDistance ? self::SHORT_DISTANCE_LABEL : self::LONG_DISTANCE_LABEL,
            'Numéro de permis de conduire' => $this->_user->getDrivingLicenseNumber(),
        ];

        if (false === $isShortDistance) {
            $data['Numéro de téléphone'] = self::LONG_DISTANCE_LABEL;
        }

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
        return new MobConnectSubscriptionResponse($this->__postSubscription($this->_apiParams->getLongDistanceSubscriptionId(), false, $this->_user->getTelephone()));
    }

    // Updates a user subscription with a carpool proof
    public function patchUserSubscription(string $subscriptionId, string $rpcJourneyId, bool $isShortDistance = false, ?\DateTimeInterface $costSharingDate = null)
    {
        // Todo: this route is not available on the provider API
        return;
        $data = [];

        if (true === $isShortDistance) {
            $data['Identifiant du trajet'] = $rpcJourneyId;
        } else {
            $data['Date de partage des frais'] = $costSharingDate->format('d/m/Y');
        }

        $this->_createDataProvider(self::ROUTE_PATCH_SUBSCRIPTIONS, $subscriptionId);

        return new MobConnectSubscriptionResponse(
            $this->_getResponse($this->_dataProvider->patchItem($data, $this->_buildHeaders($this->_user->getMobConnectAuth()->getAccessToken())))
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
