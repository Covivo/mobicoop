<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\RelaunchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProofRelaunchListener implements EventSubscriberInterface
{
    /**
     * @var RelaunchService
     */
    private $_relaunchService;

    public function __construct(RelaunchService $relaunchService)
    {
        $this->_relaunchService = $relaunchService;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
        ];
    }

    public function onProofValidated(CarpoolProofValidatedEvent $event): void
    {
        $this->_relaunchService->relaunchUserProofs($event->getCarpoolProof());
    }
}
