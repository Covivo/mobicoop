<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Resource\Incentive;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class IncentiveManager extends MobConnectManager
{
    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        if (is_null($security->getUser()) || is_null($security->getUser()->getMobConnectAuth())) {
            throw new AccessDeniedException();
        }

        $this->setDriver($security->getUser());
    }

    public function getMobConnectIncentives(): ArrayCollection
    {
        $getResponse = $this->getIncentives();

        $getResponse = null;

        return !is_null($getResponse) ? $getResponse->getIncentives() : new ArrayCollection();
    }

    public function getMobConnectIncentive(string $incentive_id): ?Incentive
    {
        $getResponse = $this->getIncentive($incentive_id);

        return new Incentive($getResponse->getId(), $getResponse->getTitle(), $getResponse->getDescription());
    }
}
