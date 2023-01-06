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
    private const PROVIDER = 'mobConnect';
    private const SSO_ACTIVATION_PATH = '/sso-activation';
    private const ERROR_MISSING_PROVIDER = 'There is no SSO service to pass to the view!';

    private $_logger;
    private $_userManager;

    public function __construct(UserManager $userManager, LoggerInterface $logger)
    {
        $this->_userManager = $userManager;
        $this->_logger = $logger;
    }

    public function assistiveDevices(Request $request)
    {
        $user = $this->getUser();

        $params = [];

        if (
            !is_null($user)
            && (
                is_null($user->getSsoId())
                || (
                    !is_null($user->getSsoId())
                    && !is_null($user->getSsoProvider())
                    && self::PROVIDER != $user->getSsoProvider()
                )
            )
        ) {
            $redirectUri = str_replace('/', '', $request->getPathInfo()).self::SSO_ACTIVATION_PATH;
            $ssoServices = $this->_userManager->getSsoService(self::PROVIDER, $redirectUri);

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
        if (!is_null($user) && self::PROVIDER === $queryParams['state']) {
            // TODO: PATCH de l'utilisateur
            $data = [
                'ssoProvider' => self::PROVIDER,
                'ssoId' => $queryParams['code'],
                'baseSiteUri' => 'http://localhost:9091',
                'redirectUri' => 'aides-a-la-mobilite'.self::SSO_ACTIVATION_PATH,
                'eec' => false,
            ];

            // return new JsonResponse($data);
            $response = $this->_userManager->patchUserForSsoAssociation($user, $data);

            // var_dump($response);

            // exit;
        }

        // exit('Fin avant redirection');

        return $this->redirectToRoute('assistive.devices');
    }
}
