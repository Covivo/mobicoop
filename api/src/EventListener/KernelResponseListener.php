<?php

namespace App\EventListener;

use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Security;

/**
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class KernelResponseListener
{
    private $_request;
    private $_security;
    private $_userManager;

    public function __construct(RequestStack $requestStack, Security $security, UserManager $userManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_security = $security;
        $this->_userManager = $userManager;
    }

    /**
     * Listener used to add specifics properties on each route except the authentication route.
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        return;
        $user = $this->_security->getUser();

        if (is_null($user) || !$user instanceof User) {
            return;
        }

        $responseContent = json_decode($event->getResponse()->getContent());

        if ('api_login_check_user' !== $this->_request->get('_route')) {
            $user = $this->_userManager->getUnreadMessageNumberForResponseInsertion($user);

            if (!property_exists($responseContent, 'unreadCarpoolMessageNumber')) {
                $responseContent->unreadCarpoolMessageNumber = $user->getUnreadCarpoolMessageNumber();
            }
            if (!property_exists($responseContent, 'unreadDirecMessageNumber')) {
                $responseContent->unreadDirectMessageNumber = $user->getUnreadDirectMessageNumber();
            }
            if (!property_exists($responseContent, 'unreadSolidaryMessageNumber')) {
                $responseContent->unreadSolidaryMessageNumber = $user->getUnreadSolidaryMessageNumber();
            }
        }

        $event->getResponse()->setContent(json_encode($responseContent));
    }
}
