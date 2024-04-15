<?php

namespace App\Incentive\Service\Stage;

use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\Subscription\SpecificFields;
use App\Incentive\Resource\EecInstance;
use App\Incentive\Validator\SubscriptionValidator;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Http\Client\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;

class PatchSubscription extends UpdateSubscription
{
    /**
     * @var User
     */
    private $_user;

    /**
     * @var string
     */
    private $_propertyToPatch;

    /**
     * @var array
     */
    private $_subscriptions = [];

    public function __construct(EntityManagerInterface $em, EecInstance $eecInstance, User $user, string $propertyToPatch)
    {
        $this->_em = $em;
        $this->_eecInstance = $eecInstance;

        $this->_user = $user;
        $this->_setPropertyToPatch($propertyToPatch);
        $this->_setSubscriptions();

        $this->_build();
    }

    public function execute()
    {
        foreach ($this->_subscriptions as $subscription) {
            $this->_subscription = $subscription;

            $httpQueryParams = $this->_getHttpQueryParams();

            try {
                $this->_apiProvider->patchSubscription($this->_subscription, $httpQueryParams);
            } catch (HttpException $exception) {
                $subscription->addLog($exception, Log::TYPE_COMMITMENT, $httpQueryParams);

                $this->_em->flush();

                continue;
            }

            $this->_em->flush();
        }
    }

    private function _getHttpQueryParams(): array
    {
        switch ($this->_propertyToPatch) {
            case SpecificFields::DRIVING_LICENCE_NUMBER:
                $this->_subscription->setDrivingLicenceNumber($this->_user->getDrivingLicenceNumber());

                return [SpecificFields::DRIVING_LICENCE_NUMBER => $this->_subscription->getDrivingLicenceNumber()];

            case SpecificFields::PHONE_NUMBER:
                $this->_subscription->setTelephone($this->_user->getTelephone());

                return [SpecificFields::PHONE_NUMBER => $this->_subscription->getTelephone()];

            default: throw new \LogicException('The sended property cannot be patched', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws \LogicException
     */
    private function _setPropertyToPatch(string $propertyToPatch): self
    {
        if (SubscriptionValidator::canPropertyBePatched($propertyToPatch)) {
            $this->_propertyToPatch = $propertyToPatch;

            return $this;
        }

        throw new \LogicException('The sended property cannot be patched', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function _setSubscriptions(): self
    {
        if (!is_null($this->_user->getShortDistanceSubscription())) {
            array_push($this->_subscriptions, $this->_user->getShortDistanceSubscription());
        }

        if (!is_null($this->_user->getLongDistanceSubscription())) {
            array_push($this->_subscriptions, $this->_user->getLongDistanceSubscription());
        }

        return $this;
    }
}
