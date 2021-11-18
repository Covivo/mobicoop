<?php

namespace Mobicoop\Bundle\MobicoopBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestSubscriber implements EventSubscriberInterface
{
	use TargetPathTrait;

	private $session;

	public function __construct(SessionInterface $session)
	{
		$this->session = $session;
	}

	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		if (
			!$event->isMasterRequest()
			|| $request->isXmlHttpRequest()
			|| $request->isMethod('POST')
			|| 'user_login' === $request->attributes->get('_route')
			|| 'user_login_delegate' === $request->attributes->get('_route')
			|| 'user_update_password_reset' === $request->attributes->get('_route')
			|| 'user_password_forgot' === $request->attributes->get('_route')
			|| 'user_password_reset' === $request->attributes->get('_route')
			|| 'home_logout' === $request->attributes->get('_route')
			|| 'user_sign_up' === $request->attributes->get('_route')
		) {
			return;
		}

		$this->saveTargetPath($this->session, 'main', $request->getUri());
	}

	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::REQUEST => ['onKernelRequest']
		];
	}
}
