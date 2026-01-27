<?php
namespace App\Controller;

use App\Action\Repository\ActionRepository;
use App\Communication\Service\NotificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EmailTestController extends AbstractController
{
    /**
     * @var Resquest;
     */
    private $_request;

    /**
     * @var NotificationManager
     */
    private $_notificationManager;

    public function __construct(RequestStack $requestStack, NotificationManager $notificationManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_notificationManager = $notificationManager;
    }


    public function displayEmailContent()
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new UnauthorizedHttpException('This operation is not authorized');
        }

        $action = $this->_request->query->get('action');
        if (is_null($action)) {
            throw new BadRequestHttpException('Missing action parameter');
        }

        $previewMode = $this->_request->query->get('previewMode') ?? true;

        $this->_notificationManager->notifies($action, $user, null, (bool)$previewMode);

        return new Response('The message has been processed successfully', Response::HTTP_OK);
    }
}
