<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Controller;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\Event\Service\EventManager;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Service\PublicTransportManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolController extends AbstractController
{
    use HydraControllerTrait;

    private $midPrice;
    private $highPrice;
    private $forbiddenPrice;
    private $defaultRole;
    private $defaultRegular;
    private $platformName;
    private $carpoolRDEXJourneys;
    private $ptResults;
    private $ptProvider;
    private $ptKey;
    private $ptAlgorithm;
    private $ptDateCriteria;
    private $ptMode;
    private $ptUsername;
    private $publicTransportManager;
    private $participationText;
    private $fraudWarningDisplay;
    private $ageDisplay;
    private $birthdateDisplay;
    private $eventManager;
    private $seatNumber;
    private $defaultSeatNumber;
    private $contentPassenger;
    private $carpoolSettingsDisplay;
    private $carpoolStandardBookingEnabled;
    private $carpoolStandardMessagingEnabled;
    private $specificTerms;
    private $defaultRoleToPublish;
    private $bothRoleEnabled;
    private $carpoolTimezone;

    public function __construct(
        PublicTransportManager $publicTransportManager,
        EventManager $eventManager,
        $midPrice,
        $highPrice,
        $forbiddenPrice,
        $defaultRole,
        $participationText,
        int $seatNumber,
        int $defaultSeatNumber,
        bool $contentPassenger,
        bool $defaultRegular,
        string $platformName,
        bool $carpoolRDEXJourneys,
        int $ptResults,
        bool $fraudWarningDisplay,
        bool $ageDisplay,
        bool $birthdateDisplay,
        bool $carpoolSettingsDisplay,
        bool $carpoolStandardBookingEnabled,
        bool $carpoolStandardMessagingEnabled,
        bool $specificTerms,
        int $defaultRoleToPublish,
        bool $bothRoleEnabled,
        string $carpoolTimezone
    ) {
        $this->midPrice = $midPrice;
        $this->highPrice = $highPrice;
        $this->forbiddenPrice = $forbiddenPrice;
        $this->defaultRole = $defaultRole;
        $this->defaultRegular = $defaultRegular;
        $this->platformName = $platformName;
        $this->carpoolRDEXJourneys = $carpoolRDEXJourneys;
        $this->ptResults = $ptResults;
        $this->publicTransportManager = $publicTransportManager;
        $this->participationText = $participationText;
        $this->fraudWarningDisplay = $fraudWarningDisplay;
        $this->ageDisplay = $ageDisplay;
        $this->birthdateDisplay = $birthdateDisplay;
        $this->eventManager = $eventManager;
        $this->seatNumber = $seatNumber;
        $this->defaultSeatNumber = $defaultSeatNumber;
        $this->contentPassenger = $contentPassenger;
        $this->carpoolSettingsDisplay = $carpoolSettingsDisplay;
        $this->carpoolStandardBookingEnabled = $carpoolStandardBookingEnabled;
        $this->carpoolStandardMessagingEnabled = $carpoolStandardMessagingEnabled;
        $this->specificTerms = $specificTerms;
        $this->defaultRoleToPublish = $defaultRoleToPublish;
        $this->bothRoleEnabled = $bothRoleEnabled;
        $this->carpoolTimezone = $carpoolTimezone;
    }

    private function __originDisplay(array $waypoint)
    {
        if (isset($waypoint[0]['name']) && '' !== trim($waypoint[0]['name'])) {
            return $waypoint[0]['name'];
        }

        return $waypoint[0]['addressLocality'];
    }

    private function __destinationDisplay(array $waypoint)
    {
        if (isset($waypoint[count($waypoint) - 1]['name']) && '' !== trim($waypoint[count($waypoint) - 1]['name'])) {
            return $waypoint[count($waypoint) - 1]['name'];
        }

        return $waypoint[count($waypoint) - 1]['addressLocality'];
    }

    private function __originDisplayFromObject($origin)
    {
        if (isset($origin->name) && '' !== $origin->name) {
            return $origin->name;
        }
        if (isset($origin->addressLocality) && '' !== $origin->addressLocality) {
            return $origin->addressLocality;
        }
    }

    private function __destinationDisplayFromObject($destination)
    {
        if (isset($destination->name) && '' !== $destination->name) {
            return $destination->name;
        }
        if (isset($destination->addressLocality) && '' !== $destination->addressLocality) {
            return $destination->addressLocality;
        }
    }

    /**
     * Create a carpooling ad.
     */
    public function carpoolAdPost(AdManager $adManager, Request $request, UserManager $userManager)
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);

        $user = $userManager->getLoggedUser();

        // Redirect to user_login
        if (!$user instanceof User) {
            $user = null;

            return $this->redirectToRoute('user_login');
        }

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $result = $adManager->createAd($data);
            if ($result instanceof Ad) {
                return $this->json(['result' => $result]);
            }

            return $this->json($result);
        }

        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            'pricesRange' => [
                'mid' => $this->midPrice,
                'high' => $this->highPrice,
                'forbidden' => $this->forbiddenPrice,
            ],
            'participationText' => $this->participationText,
            'ageDisplay' => $this->ageDisplay,
            'seatNumber' => $this->seatNumber,
            'defaultSeatNumber' => $this->defaultSeatNumber,
            'contentPassenger' => $this->contentPassenger,
            'specificTerms' => $this->specificTerms,
            'defaultRoleToPublish' => $this->defaultRoleToPublish,
            'bothRoleEnabled' => $this->bothRoleEnabled,
        ]);
    }

    /**
     * Update a carpooling ad.
     */
    public function carpoolAdUpdate(int $id, AdManager $adManager, Request $request)
    {
        $ad = $adManager->getFullAd($id);
        $this->denyAccessUnlessGranted('update_ad', $ad);

        $hasAsks = false;
        $hasPotentialAds = false;
        if ($ad->getPotentialCarpoolers() > 0) {
            $hasPotentialAds = true;
        }
        if (count($ad->getAsks()) > 0) {
            $hasAsks = true;
        }

        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);
            $data['mailSearchLink'] = $this->generateUrl('carpool_search_result_get', [], UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->json(['result' => $adManager->updateAd($data, $ad)]);
        }

        return $this->render('@Mobicoop/carpool/update.html.twig', [
            'ad' => $ad,
            'communityIds' => array_map(function ($community) {
                return $community['id'];
            }, $ad->getCommunities()),
            'hasAsks' => $hasAsks,
            'hasPotentialAds' => $hasPotentialAds,
            'solidaryExclusive' => $ad->isSolidaryExclusive(),
            'seatNumber' => $this->seatNumber,
            'defaultRoleToPublish' => $this->defaultRoleToPublish,
            'bothRoleEnabled' => $this->bothRoleEnabled,
        ]);
    }

    public function carpoolAdCleanOrphanProposals(Request $request, AdManager $adManager)
    {
        if ($request->isMethod('POST')) {
            $adManager->cleanOrphanUserProposals();
        }

        return new JsonResponse();
    }

    /**
     * Save a carpooling search.
     */
    public function carpoolSearchSave(int $id, AdManager $adManager, Request $request)
    {
        $ad = $adManager->getFullAd($id);
        $this->denyAccessUnlessGranted('update_ad', $ad);

        $hasAsks = false;
        $hasPotentialAds = false;
        if ($ad->getPotentialCarpoolers() > 0) {
            $hasPotentialAds = true;
        }
        if (count($ad->getAsks()) > 0) {
            $hasAsks = true;
        }

        return $this->render('@Mobicoop/carpool/saveSearch.html.twig', [
            'ad' => $ad,
            'hasAsks' => $hasAsks,
            'hasPotentialAds' => $hasPotentialAds,
            'solidaryExclusive' => $ad->isSolidaryExclusive(),
            'specificTerms' => $this->specificTerms,
            'participationText' => $this->participationText,
            'defaultRoleToPublish' => $this->defaultRoleToPublish,
            'bothRoleEnabled' => $this->bothRoleEnabled,
        ]);
    }

    /**
     * Create the first carpooling ad.
     */
    public function carpoolFirstAdPost()
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_first_ad', $ad);

        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            'firstAd' => true,
            'pricesRange' => [
                'mid' => $this->midPrice,
                'high' => $this->highPrice,
                'forbidden' => $this->forbiddenPrice,
            ],
            'regular' => $this->defaultRegular,
            'participationText' => $this->participationText,
            'ageDisplay' => $this->ageDisplay,
            'seatNumber' => $this->seatNumber,
            'defaultSeatNumber' => $this->defaultSeatNumber,
            'contentPassenger' => $this->contentPassenger,
            'specificTerms' => $this->specificTerms,
            'defaultRoleToPublish' => $this->defaultRoleToPublish,
            'bothRoleEnabled' => $this->bothRoleEnabled,
        ]);
    }

    /**
     * Create a solidary exclusive carpooling ad.
     */
    public function carpoolSolidaryExclusiveAdPost()
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);

        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'solidaryExclusiveAd' => true,
                'pricesRange' => [
                    'mid' => $this->midPrice,
                    'high' => $this->highPrice,
                    'forbidden' => $this->forbiddenPrice,
                ],
                'regular' => $this->defaultRegular,
                'participationText' => $this->participationText,
                'ageDisplay' => $this->ageDisplay,
                'seatNumber' => $this->seatNumber,
                'defaultSeatNumber' => $this->defaultSeatNumber,
                'contentPassenger' => $this->contentPassenger,
                'specificTerms' => $this->specificTerms,
                'defaultRoleToPublish' => $this->defaultRoleToPublish,
                'bothRoleEnabled' => $this->bothRoleEnabled,
            ]
        );
    }

    /**
     * Create a carpooling ad from a search component (home, community...) or event component
     * (POST).
     */
    public function carpoolAdPostFromSearch(Request $request, ?int $eventId = null)
    {
        // init destination if provided
        $destination = $request->request->get('destination');

        $regular = $request->request->get('regular')
            ? json_decode($request->request->get('regular'))
            : !is_null($request->get('eventId'))
                ? false
                : $this->defaultRegular;

        // ad for an event ?
        if (!is_null($eventId) && $event = $this->eventManager->getEvent($eventId)) {
            $destination = json_encode($event->getAddress());
        } else {
            // force eventId to null if event doesn't exist !
            $eventId = null;
        }

        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communityIds' => $request->request->get('communityId') ? [(int) $request->request->get('communityId')] : null,
                'origin' => $request->request->get('origin'),
                'destination' => $destination,
                'eventId' => $eventId,
                'regular' => $regular,
                'date' => $request->request->get('date'),
                'time' => $request->request->get('time'),
                'pricesRange' => [
                    'mid' => $this->midPrice,
                    'high' => $this->highPrice,
                    'forbidden' => $this->forbiddenPrice,
                ],
                'participationText' => $this->participationText,
                'ageDisplay' => $this->ageDisplay,
                'seatNumber' => $this->seatNumber,
                'defaultSeatNumber' => $this->defaultSeatNumber,
                'contentPassenger' => $this->contentPassenger,
                'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
                'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
                'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
                'specificTerms' => $this->specificTerms,
                'defaultRoleToPublish' => $this->defaultRoleToPublish,
                'bothRoleEnabled' => $this->bothRoleEnabled,
            ]
        );
    }

    /**
     * Delete a carpooling ad.
     *
     * @return JsonResponse
     */
    public function carpoolAdDelete(AdManager $adManager, Request $request, UserManager $userManager)
    {
        if ($request->isMethod('DELETE')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['adId'])) {
                return new JsonResponse([
                    'message' => 'error',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // add the id of the deleter
            $data['deleterId'] = $userManager->getLoggedUser()->getId();

            return $this->json($adManager->deleteAd($data['adId'], $data));
        }
    }

    /**
     * Ad results.
     * (POST).
     *
     * @param mixed $id
     */
    public function carpoolAdResults($id, AdManager $adManager)
    {
        $ad = $adManager->getAd($id);

        $origin = $this->__originDisplay($ad->getOutwardWaypoints());
        $destination = $this->__destinationDisplay($ad->getOutwardWaypoints());

        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'proposalId' => $id,
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => false, // No RDEX, this not a new search
            'ptSearch' => false, // No PT Results, this not a new search
            'defaultRole' => $this->defaultRole,
            'fraudWarningDisplay' => $this->fraudWarningDisplay,
            'originTitle' => $origin,
            'destinationTitle' => $destination,
            'ageDisplay' => $this->ageDisplay,
            'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
            'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
            'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
        ]);
    }

    /**
     * Ad results after authentication.
     * (POST).
     *
     * @param mixed $id
     */
    public function carpoolAdResultsAfterAuthentication($id, AdManager $adManager)
    {
        // we need to claim the source proposal, as it should be anonymous
        if ($adManager->claimAd($id)) {
            $ad = $adManager->getAd($id);

            $origin = $this->__originDisplay($ad->getOutwardWaypoints());
            $destination = $this->__destinationDisplay($ad->getOutwardWaypoints());

            return $this->render('@Mobicoop/carpool/results.html.twig', [
                'proposalId' => $id,
                'platformName' => $this->platformName,
                'externalRDEXJourneys' => false, // No RDEX, this not a new search
                'ptSearch' => false, // No PT Results, this not a new search
                'defaultRole' => $this->defaultRole,
                'fraudWarningDisplay' => $this->fraudWarningDisplay,
                'originTitle' => $origin,
                'destinationTitle' => $destination,
                'ageDisplay' => $this->ageDisplay,
                'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
                'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
                'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
            ]);
        }

        // for now if the claim fails we redirect to home !
        return $this->redirectToRoute('home');
    }

    /**
     * Ad result detail data.
     * (AJAX).
     *
     * @param mixed $id
     */
    public function carpoolAdDetail($id, AdManager $adManager, Request $request)
    {
        $filters = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['filters'])) {
                $filters = $data['filters'];
            }
        }
        if ($ad = $adManager->getAd($id, $filters)) {
            $origin = $this->__originDisplay($ad->getOutwardWaypoints());
            $destination = $this->__destinationDisplay($ad->getOutwardWaypoints());

            // $this->denyAccessUnlessGranted('results_ad', $ad);
            return $this->json([
                'origin' => $origin,
                'destination' => $destination,
                'results' => $ad->getResults(),
                'nb' => $ad->getNbResults(),
            ]);
        }

        return $this->json([]);
    }

    /**
     * Ad result detail data from external link.
     * (AJAX).
     *
     * @param mixed $id
     */
    public function carpoolAdDetailExternal($id, AdManager $adManager, Request $request)
    {
        $filters = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['filters'])) {
                $filters = $data['filters'];
            }
        }
        if ($ad = $adManager->getAdFromExternalId($id, $filters)) {
            // $this->denyAccessUnlessGranted('results_ad', $ad);
            return $this->json($ad->getResults());
        }

        return $this->json([]);
    }

    /**
     * Simple search results.
     * (POST).
     */
    public function carpoolSearchResult(Request $request, UserManager $userManager)
    {
        $origin = json_decode($request->request->get('origin'));
        $destination = json_decode($request->request->get('destination'));
        $originTitle = $this->__originDisplayFromObject($origin);
        $destinationTitle = $this->__destinationDisplayFromObject($destination);

        $thresholdIsReached = $this->publicTransportManager->checkThreshold($origin->latitude, $origin->longitude);

        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'origin' => $request->request->get('origin'),
            'destination' => $request->request->get('destination'),
            'date' => $request->request->get('date'),
            'time' => $request->request->get('time'),
            'regular' => $request->request->get('regular'),
            'communityId' => $request->request->get('communityId'),
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
            'ptSearch' => $this->ptResults && !$thresholdIsReached['thresholdReached'],
            'defaultRole' => $this->defaultRole,
            'fraudWarningDisplay' => $this->fraudWarningDisplay,
            'originTitle' => $originTitle,
            'destinationTitle' => $destinationTitle,
            'ageDisplay' => $this->ageDisplay,
            'birthdateDisplay' => $this->birthdateDisplay,
            'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
            'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
            'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
        ]);
    }

    /**
     * Simple search results (GET).
     *
     * @param Request     $request     The request
     * @param UserManager $userManager The userManager
     *
     * @return null|Response The response
     */
    public function carpoolSearchResultGet(Request $request, UserManager $userManager)
    {
        $origin = json_decode($request->get('origin'));
        $destination = json_decode($request->get('destination'));
        $originTitle = $this->__originDisplayFromObject($origin);
        $destinationTitle = $this->__destinationDisplayFromObject($destination);

        $thresholdIsReached = $this->publicTransportManager->checkThreshold($origin->latitude, $origin->longitude);

        return $this->render('@Mobicoop/carpool/results.html.twig', [
            // todo: use if we can keep the proposal (request or offer) if we delete the matched one - cf CarpoolSubscriber
            //            'proposalId' => $request->get('pid'),
            'origin' => $request->get('origin'),
            'destination' => $request->get('destination'),
            'date' => $request->get('date'),
            'time' => $request->request->get('time'),
            'regular' => (bool) $request->get('regular'),
            'communityId' => $request->get('cid'),
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
            'ptSearch' => $this->ptResults && !$thresholdIsReached['thresholdReached'],
            'defaultRole' => $this->defaultRole,
            'fraudWarningDisplay' => $this->fraudWarningDisplay,
            'originTitle' => $originTitle,
            'destinationTitle' => $destinationTitle,
            'ageDisplay' => $this->ageDisplay,
            'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
            'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
            'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
            'includePassenger' => filter_var($request->query->get('includePassenger'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * RDEX search results (public GET link).
     *
     * @param Request     $request     The request
     * @param UserManager $userManager The userManager
     * @param string      $externalId  The external ID of the proposal that was generated for the external search
     *
     * @return null|Response The response
     */
    public function carpoolSearchResultFromRdexLink(Request $request, UserManager $userManager, string $externalId, AdManager $adManager, Deserializer $deserializer)
    {
        $ad = $adManager->getAdFromExternalId($externalId);
        $origin = $ad->getOutwardWaypoints()[0]['address']['addressLocality'];
        $destination = $ad->getOutwardWaypoints()[count($ad->getOutwardWaypoints()) - 1]['address']['addressLocality'];

        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'externalId' => $externalId,
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => false, // No External Rdex result for an RDex external link
            'ptSearch' => false, // No PT Results, this not a new search
            'defaultRole' => $this->defaultRole,
            'fraudWarningDisplay' => $this->fraudWarningDisplay,
            'originTitle' => $origin,
            'originLiteral' => $origin,
            'destinationTitle' => $destination,
            'destinationLiteral' => $destination,
            'ageDisplay' => $this->ageDisplay,
            'carpoolSettingsDisplay' => $this->carpoolSettingsDisplay,
            'carpoolStandardBookingEnabled' => $this->carpoolStandardBookingEnabled,
            'carpoolStandardMessagingEnabled' => $this->carpoolStandardMessagingEnabled,
        ]);
    }

    /**
     * Community proposal search results (public GET link)
     * A proposal ID must be given, we need to check if the current user has the right on this community proposal,
     * then we create a new proposal with the same origin/destination than the given proposal.
     *
     * @param Request     $request             The request
     * @param UserManager $userManager         The userManager
     * @param int         $communityProposalId The community proposal ID from which we want to make a search
     *
     * @return null|Response The response
     */
    public function carpoolSearchResultFromCommunityProposal(Request $request, UserManager $userManager, AdManager $adManager, int $communityProposalId)
    {
        // TODO : check the auth
        // TODO : get the original ad
        // $ad = $adManager->getAd($communityProposalId);
        // $origin = $ad->getOutwardWaypoints()->???;
        // $destination = $ad->getOutwardWaypoints()->???;
        // return $this->render('@Mobicoop/carpool/results.html.twig', [
        //     'origin' => $origin,
        //     'destination' => $destination,
        //     'date' => $request->get('date'),
        //     'regular' => (bool) $request->get('regular'),
        //     'communityId' => $request->get('cid'),
        //     'user' => $userManager->getLoggedUser(),
        //     'platformName' => $this->platformName,
        //     'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
        //     'defaultRole'=>$this->defaultRole
        // ]);
    }

    /**
     * Matching Search
     * (AJAX POST).
     */
    public function carpoolSearchMatching(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);
        $date = null;
        if (isset($params['date']) && '' != $params['date']) {
            $date = new \DateTime($params['date']);
        }
        $time = null;
        if (isset($params['time']) && '' != $params['time']) {
            $time = new \DateTime($params['time']);
            if (!$time) {
                $time = \DateTime::createFromFormat('H:i', $params['time']);
            }
        }
        $regular = isset($params['regular']) ? $params['regular'] : false;
        $strictDate = isset($params['strictDate']) ? $params['strictDate'] : null;
        $strictPunctual = isset($params['strictPunctual']) ? $params['strictPunctual'] : null;
        $strictRegular = isset($params['strictRegular']) ? $params['strictRegular'] : null;
        $role = isset($params['role']) ? $params['role'] : $this->defaultRole;
        $userId = isset($params['userId']) ? $params['userId'] : null;
        $communityId = isset($params['communityId']) ? $params['communityId'] : null;

        $filters = isset($params['filters']) ? $params['filters'] : null;

        $result = [];
        if ($ad = $adManager->getResultsForSearch(
            $params['origin'],
            $params['destination'],
            $date,
            (!$time) ? null : $time,
            $regular,
            $strictDate,
            $strictPunctual,
            $strictRegular,
            $role,
            $userId,
            $communityId,
            $filters
        )) {
            $result = [
                'results' => $ad->getResults(),
                'nb' => $ad->getNbResults(),
                'searchId' => $ad->getId(),
            ];
        }

        return $this->json($result);
    }

    /**
     * Formal ask from carpool results
     * (AJAX POST).
     */
    public function carpoolAsk(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);

        $ask = $adManager->createAsk($params, true);

        if (!is_null($ask)) {
            return $this->json($ask);
        }

        return $this->json('error');
    }

    /**
     * Provider rdex.
     */
    public function rdexProvider(ExternalJourneyManager $externalJourneyManager)
    {
        return $this->json($externalJourneyManager->getExternalJourneyProviders(DataProvider::RETURN_JSON));
    }

    /**
     * Journey rdex.
     */
    public function rdexJourney(ExternalJourneyManager $externalJourneyManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return $this->json($externalJourneyManager->getExternalJourney($data, DataProvider::RETURN_JSON));
        }

        return $this->json('');
    }

    /**
     * Journey rdex.
     */
    public function rdexConnection(ExternalJourneyManager $externalJourneyManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return $this->json($externalJourneyManager->postExternalConnection($data, DataProvider::RETURN_JSON));
        }

        return $this->json('');
    }

    /**
     * Public Transport search (POST).
     */
    public function PTSearch(Request $request)
    {
        $params = json_decode($request->getContent(), true);

        // If there is no date in params, we use 'now'
        $date = new \DateTime('now', new \DateTimeZone($this->carpoolTimezone));
        if (!empty($params['date'])) {
            $date = new \DateTime($params['date'].' 08:00:00', new \DateTimeZone($this->carpoolTimezone));
        }
        $journeys = $this->publicTransportManager->getJourneys(
            $params['from_latitude'],
            $params['from_longitude'],
            $params['to_latitude'],
            $params['to_longitude'],
            $date->format(\DateTime::RFC3339)
        );

        if (!is_null($journeys)) {
            return $this->json($journeys);
        }

        return $this->json('error');
    }
}
