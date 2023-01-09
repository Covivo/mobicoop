<?php

namespace Mobicoop\Bundle\MobicoopBundle\AssistiveDevices\Controller;

use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AssistiveController extends AbstractController
{
    private const PROVIDER = 'mobConnectBasic';

    private const ERROR_MISSING_PROVIDER = 'There is no SSO service to pass to the view!';

    private $_assistiveSsoProvider;
    private $_logger;
    private $_userManager;

    public function __construct(UserManager $userManager, LoggerInterface $logger, string $assistiveSsoProvider)
    {
        $this->_userManager = $userManager;
        $this->_logger = $logger;
        $this->_assistiveSsoProvider = $assistiveSsoProvider;
    }

    public function assistiveDevices(Request $request)
    {
        $user = $this->getUser();

        $params = [];

        // return new JsonResponse($this->getUser());
        if (
            !is_null($user)
            && (
                is_null($user->getSsoId())
                || (
                    !is_null($user->getSsoId())
                    && !is_null($user->getSsoProvider())
                    && $this->_assistiveSsoProvider != $user->getSsoProvider()
                )
            )
        ) {
            $ssoServices = $this->_userManager->getSsoService($this->_assistiveSsoProvider);

            if (empty($ssoServices)) {
                $this->_logger->error(self::ERROR_MISSING_PROVIDER);

                throw new UnprocessableEntityHttpException(self::ERROR_MISSING_PROVIDER);
            }

            // return new JsonResponse($this->getUser());
            // return new JsonResponse($ssoServices);
            $params['activationUri'] = $ssoServices[0]->getUri();
        }

        return $this->render(
            '@Mobicoop/assistiveDevices/assistive.html.twig',
            $params
        );
    }

    public function ssoActivation(Request $request)
    {
        // return new JsonResponse($this->getUser());
        $user = $this->getUser();
        $queryParams = $request->query->all();

        // return new JsonResponse($queryParams);
        if (!is_null($user) && $this->_assistiveSsoProvider === $queryParams['state']) {
            // TODO: PATCH de l'utilisateur
            $data = [
                'ssoProvider' => $this->_assistiveSsoProvider,
                'ssoId' => $queryParams['code'],
                'baseSiteUri' => 'http://localhost:9091',
                'eec' => false,
            ];

            // return new JsonResponse($data);
            // TODO: La mise à jour de l'utilisateur est fonctionnelle mais constinue malgré tout de créer les souscription CEE.
            $this->_userManager->patchUserForSsoAssociation($user, $data);
        }

        // exit('Fin avant redirection');

        return $this->redirectToRoute('assistive.devices');
    }
}
