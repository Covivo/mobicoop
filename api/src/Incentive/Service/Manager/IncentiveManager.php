<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Resource\Incentive;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class IncentiveManager extends MobConnectManager
{
    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        InstanceManager $instanceManager,
        LoggerService $loggerService
    ) {
        parent::__construct($em, $instanceManager, $loggerService);

        /**
         * @var User
         */
        $user = $security->getUser();

        if (is_null($user) || !$user instanceof User || is_null($user->getMobConnectAuth())) {
            throw new AccessDeniedException('Access denied - The user must be authenticated and have subscribed to the incentives to access this resource');
        }

        $this->setDriver($security->getUser());
    }

    public function getMobConnectIncentives(): ArrayCollection
    {
        $getResponse = $this->getIncentives($this->getDriver());

        return !is_null($getResponse) ? $getResponse->getIncentives() : new ArrayCollection();
    }

    public function getMobConnectIncentive(string $incentiveId): ?Incentive
    {
        $getResponse = $this->getIncentive($incentiveId, $this->getDriver());

        return new Incentive($getResponse->getId(), $getResponse->getType(), $getResponse->getTitle(), $getResponse->getDescription(), $getResponse->getSubscriptionLink());
    }
}
