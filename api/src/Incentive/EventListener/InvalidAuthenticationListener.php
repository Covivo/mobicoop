<?php

namespace App\Incentive\EventListener;

use App\Incentive\Event\InvalidAuthenticationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvalidAuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            InvalidAuthenticationEvent::NAME => 'onInvalidAuthentication',
        ];
    }

    public function onInvalidAuthentication(InvalidAuthenticationEvent $event)
    {
        $user = $event->getUser();

        $user->getMobConnectAuth()->setValidity(false);

        $this->_em->flush();
    }
}
