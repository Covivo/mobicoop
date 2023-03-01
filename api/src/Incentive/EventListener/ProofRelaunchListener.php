<?php

namespace App\Incentive\EventListener;

use App\Carpool\Event\CarpoolProofValidatedEvent;
use App\Incentive\Service\Manager\RelaunchManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProofRelaunchListener implements EventSubscriberInterface
{
    /**
     * @var RelaunchManager
     */
    private $_relaunchManager;

    public function __construct(RelaunchManager $relaunchManager)
    {
        $this->_relaunchManager = $relaunchManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofValidatedEvent::NAME => 'onProofValidated',
        ];
    }

    public function onProofValidated(CarpoolProofValidatedEvent $event): void
    {
        $this->_relaunchManager->relaunchUserProofs($event->getCarpoolProof());
    }
}
