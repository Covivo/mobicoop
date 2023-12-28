<?php

namespace App\Incentive\Service\Definition;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Repository\LongDistanceSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Definition of a subscription to a standard long distance form as validated by the French government.
 */
class LdImproved extends SubscriptionDefinition
{
    protected const DEADLINE = '2024-01-01 00:00:00';

    protected const TRANSITIONAL_PERIOD_DURATION = 1;

    protected const MAXIMUM_JOURNEY_NUMBER = 3;

    public static function isReady(): bool
    {
        return self::getDeadline() < new \DateTime();
    }

    public static function manageTransition(...$params): void
    {
        if (
            empty($params)
            || (!isset($params[0]) || !$params[0] instanceof EntityManagerInterface)
            || (!isset($params[1]) || !$params[1] instanceof LongDistanceSubscriptionRepository)
        ) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The current definition of parameters does not allow the current process to operate');
        }

        /**
         * @var EntityManagerInterface
         */
        $em = $params[0];

        /**
         * @var LongDistanceSubscriptionRepository
         */
        $repository = $params[1];

        $now = new \DateTime();

        $dayAfterTransitionalEndDate = clone self::getTransitionalPeriodEndDate();
        $dayAfterTransitionalEndDate = $dayAfterTransitionalEndDate->add(new \DateInterval('P1D'));

        // The process is only executed once, the day after the end date of the transitional period.
        if ($now->format('Y-m-d') !== $dayAfterTransitionalEndDate->format('Y-m-d')) {
            return;
        }

        /**
         * @var LongDistanceSubscription[]
         */
        $subscriptions = array_merge(
            $repository->subscriptionsWithoutJourney(self::getDeadline()),                                                      // No commitment journey published
            $repository->subscriptionsWithUnrealizedJourneys(self::getDeadline(), self::getTransitionalPeriodEndDate()),        // Commitment journey published in 2023 for completion before deadline but not completed
            $repository->subscriptionsWithJourneysAfterExpiry(self::getDeadline(), self::getTransitionalPeriodEndDate()),       // Commitment journey published in 2023 for completion after deadline
            $repository->subscriptionsWithJourneysPublishedAfterExpiry(self::getDeadline())                                     // Commitment journey published in 2024
        );

        // These subscription need to change version
        foreach ($subscriptions as $subscription) {
            $nextDefinition = self::getNextDefinition($subscription);

            if (!is_null($nextDefinition)) {
                $subscription->setVersion($nextDefinition->getVersion());
                $subscription->setMaximumJourneysNumber($nextDefinition->getMaximumJourneysNumber());
                $subscription->setValidityPeriodDuration($nextDefinition->getValidityPeriodDuration());
            }
        }

        $em->flush();
    }

    private static function getNextDefinition(LongDistanceSubscription $subscription): ?SubscriptionDefinitionInterface
    {
        $subscriptionDefinitions = $subscription::getAvailableDefinitions();

        $currentDefintionKey = array_search(self::class, $subscriptionDefinitions);
        $nextDefinitionKey = ++$currentDefintionKey;

        if (!isset($subscriptionDefinitions[$nextDefinitionKey])) {
            return null;
        }

        return new $subscriptionDefinitions[$nextDefinitionKey]();
    }
}
