<?php

namespace Mobicoop\Bundle\MobicoopBundle\Incentive\Controller;

use Mobicoop\Bundle\MobicoopBundle\Incentive\Entity\Incentive;
use Mobicoop\Bundle\MobicoopBundle\Incentive\Service\IncentiveManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AssistiveController extends AbstractController
{
    private const ERROR_MISSING_PROVIDER = 'There is no SSO service to pass to the view!';

    private $_assistiveSsoProvider;
    private $_logger;
    private $_userManager;

    /**
     * @var IncentiveManager
     */
    private $_incentiveManager;

    public function __construct(UserManager $userManager, LoggerInterface $logger, IncentiveManager $incentiveManager, string $assistiveSsoProvider)
    {
        $this->_userManager = $userManager;
        $this->_logger = $logger;
        $this->_incentiveManager = $incentiveManager;

        $this->_assistiveSsoProvider = $assistiveSsoProvider;
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
                    && $this->_assistiveSsoProvider != $user->getSsoProvider()
                )
            )
        ) {
            $ssoServices = $this->_userManager->getSsoService($this->_assistiveSsoProvider);

            if (empty($ssoServices)) {
                $this->_logger->error(self::ERROR_MISSING_PROVIDER);

                return $this->redirectToRoute('home');
            }

            $params['activationUri'] = $ssoServices[0]->getUri();
        }

        return $this->render(
            '@Mobicoop/assistiveDevices/assistive.html.twig',
            $params
        );
    }

    public function ssoActivation(Request $request)
    {
        $user = $this->getUser();
        $queryParams = $request->query->all();

        if (!is_null($user) && $this->_assistiveSsoProvider === $queryParams['state']) {
            $data = [
                'ssoProvider' => $this->_assistiveSsoProvider,
                'ssoId' => $queryParams['code'],
                'baseSiteUri' => $request->getScheme().'://'.$request->getHost(),       // In dev mode you need to add the instance port to the baseSiteUri. For example `.':9091'`
                'eec' => false,
            ];

            $this->_userManager->patchUserForSsoAssociation($user, $data);
        }

        return $this->redirectToRoute('assistive.devices');
    }

    public function incentives()
    {
        if (is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        return $this->render(
            '@Mobicoop/assistiveDevices/incentives-list.html.twig',
            [
                'resourcePath' => Incentive::RESOURCE_NAME,
            ]
        );
    }

    public function getIncentiveAsXMLRequest(Request $request)
    {
        if (is_null($this->getUser())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->_incentiveManager->getIncentives());
    }

    public function incentive($incentive_id)
    {
        if (is_null($this->getUser())) {
            return $this->redirectToRoute('home');
        }

        $incentive = $this->_incentiveManager->getIncentive($incentive_id);

        return $this->render(
            '@Mobicoop/assistiveDevices/incentive-details.html.twig',
            [
                'incentive' => $this->_incentiveManager->getIncentive($incentive_id),
            ]
        );
    }
}
