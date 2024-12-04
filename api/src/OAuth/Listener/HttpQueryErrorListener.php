<?php

namespace App\OAuth\Listener;

use App\OAuth\Event\HttpQueryErrorEvent;
use App\OAuth\Service\ErrorsWriter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HttpQueryErrorListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            HttpQueryErrorEvent::NAME => 'onHttpQueryError',
        ];
    }

    public function onHttpQueryError(HttpQueryErrorEvent $event)
    {
        ErrorsWriter::write($event->getError());

        // TODO: Evaluate whether to send an alert when an exception is thrown
    }
}
