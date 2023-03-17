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

namespace App\Carpool\Service;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Event\AdRenewalEvent;
use App\Carpool\Event\AskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedEvent;
use App\Carpool\Event\DriverAskAdDeletedUrgentEvent;
use App\Carpool\Event\PassengerAskAdDeletedEvent;
use App\Carpool\Event\PassengerAskAdDeletedUrgentEvent;
use App\Carpool\Event\ProposalPostedEvent;
use App\Carpool\Exception\AdException;
use App\Carpool\Repository\CriteriaRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Communication\Service\InternalMessageManager;
use App\DataProvider\Entity\MobicoopMatcherProvider;
use App\DataProvider\Entity\Response;
use App\Geography\Entity\Address;
use App\Geography\Interfaces\GeorouterInterface;
use App\Geography\Service\Geocoder\MobicoopGeocoder;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\GeoTools;
use App\Geography\Service\Point\MobicoopGeocoderPointProvider;
use App\Import\Entity\UserImport;
use App\Service\FormatDataManager;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Proposal manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ProposalManager
{
    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;
    public const ROLE_BOTH = 3;

    public const OUTDATED_SEARCHES_AFTER_DAYS = 30;
    public const OUTDATED_SEARCHES_EXECUTION_LIMIT_IN_SECONDS = 7200;
    public const REMOVE_ORPHANS_EXECUTION_LIMIT_IN_SECONDS = 21600;
    public const OPTIMIZE_EXECUTION_LIMIT_IN_SECONDS = 21600;
    public const OUTDATED_SEARCHES_MEMORY_LIMIT_IN_MO = 8192;
    public const REMOVE_ORPHANS_MEMORY_LIMIT_IN_MO = 8192;
    public const OPTIMIZE_MEMORY_LIMIT_IN_MO = 8192;
    public const CHECK_OUTDATED_SEARCHES_RUNNING_FILE = 'outdatedSearches.txt';
    public const CHECK_REMOVE_ORPHANS_RUNNING_FILE = 'removeOrphans.txt';
    public const HOMOGENIZE_REGULAR_PROPOSAL_ADDRESS_DISTANCE = 5000;

    private $entityManager;
    private $proposalMatcher;
    private $proposalRepository;
    private $matchingRepository;
    private $geoRouter;
    private $logger;
    private $eventDispatcher;
    private $askManager;
    private $resultManager;
    private $formatDataManager;
    private $params;
    private $internalMessageManager;
    private $criteriaRepository;
    private $actionRepository;
    private $mobicoopGeocoderPointProvider;
    private $geoTools;
    private $mobicoopMatcherProvider;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProposalMatcher $proposalMatcher,
        ProposalRepository $proposalRepository,
        MatchingRepository $matchingRepository,
        GeoRouter $geoRouter,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher,
        AskManager $askManager,
        ResultManager $resultManager,
        FormatDataManager $formatDataManager,
        InternalMessageManager $internalMessageManager,
        CriteriaRepository $criteriaRepository,
        ActionRepository $actionRepository,
        MobicoopGeocoder $mobicoopGeocoder,
        GeoTools $geoTools,
        MobicoopMatcherProvider $mobicoopMatcherProvider,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->proposalMatcher = $proposalMatcher;
        $this->proposalRepository = $proposalRepository;
        $this->matchingRepository = $matchingRepository;
        $this->geoRouter = $geoRouter;
        $this->logger = $logger;
        $this->eventDispatcher = $dispatcher;
        $this->askManager = $askManager;
        $this->resultManager = $resultManager;
        $this->resultManager->setParams($params);
        $this->formatDataManager = $formatDataManager;
        $this->params = $params;
        $this->internalMessageManager = $internalMessageManager;
        $this->criteriaRepository = $criteriaRepository;
        $this->actionRepository = $actionRepository;
        $this->mobicoopGeocoderPointProvider = new MobicoopGeocoderPointProvider($mobicoopGeocoder);
        $this->geoTools = $geoTools;
        $this->mobicoopMatcherProvider = $mobicoopMatcherProvider;
    }

    /**
     * Get a proposal by its id.
     *
     * @param int $id The id
     *
     * @return null|Proposal The proposal found or null
     */
    public function get(int $id)
    {
        return $this->proposalRepository->find($id);
    }

    /**
     * Get a proposal by its external id.
     *
     * @param string $id The external id
     *
     * @return null|Proposal The proposal found or null
     */
    public function getFromExternalId(string $id)
    {
        return $this->proposalRepository->findOneBy(['externalId' => $id]);
    }

    /**
     * Get the last unfinished dynamic ad for a user.
     *
     * @param User $user The user
     *
     * @return null|Proposal The proposal found or null if not found
     */
    public function getLastDynamicUnfinished(User $user)
    {
        if ($lastUnfinishedProposal = $this->proposalRepository->findBy(['user' => $user, 'dynamic' => true, 'finished' => false], ['createdDate' => 'DESC'], 1)) {
            return $lastUnfinishedProposal[0];
        }

        return null;
    }

    /**
     * Prepare a proposal for persist.
     * Used when posting a proposal to populate default values like proposal validity.
     */
    public function prepareProposal(Proposal $proposal, string $matchingAlgorithm = Ad::MATCHING_ALGORITHM_V2): Proposal
    {
        return $this->treatProposal($this->setDefaults($proposal), true, $proposal->isPrivate() ? false : true, $matchingAlgorithm);
    }

    /**
     * Treat a proposal.
     *
     * @param Proposal $proposal            The proposal to treat
     * @param bool     $persist             If we persist the proposal in the database (false for a simple search)
     * @param bool     $excludeProposalUser Exclude the matching proposals made by the proposal user
     * @param string   $matchingAlgorithm   Version of the matching algorithm
     *
     * @return Proposal The treated proposal
     */
    public function treatProposal(Proposal $proposal, $persist = true, bool $excludeProposalUser = true, string $matchingAlgorithm = Ad::MATCHING_ALGORITHM_V2)
    {
        $this->logger->info('ProposalManager : treatProposal '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // set min and max times
        $proposal = $this->setMinMax($proposal);

        // set the directions
        $proposal = $this->setDirections($proposal);

        // we have the directions, we can compute the lacking prices
        $proposal = $this->setPrices($proposal);

        if ($persist) {
            $this->logger->info('ProposalManager : start persist before creating matchings'.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->entityManager->persist($proposal);
            $this->entityManager->flush();

            $this->logger->info('ProposalManager : end persist before creating matchings'.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        // matching analyze
        if (Ad::MATCHING_ALGORITHM_V2 == $matchingAlgorithm) {
            $proposal = $this->proposalMatcher->createMatchingsForProposal($proposal, $excludeProposalUser);
        } elseif (Ad::MATCHING_ALGORITHM_V3 == $matchingAlgorithm) {
            if ($proposal->isPrivate()) {
                $proposal = $this->mobicoopMatcherProvider->match($proposal);
            }
        }

        if ($persist) {
            $this->logger->info('ProposalManager : start persist '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            // TODO : here we should remove the previously matched proposal if they already exist
            $this->entityManager->persist($proposal);
            $this->entityManager->flush();
            $this->logger->info('ProposalManager : end persist '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            //  we dispatch gamification event associated
            if (!$proposal->isPrivate() && Proposal::TYPE_RETURN != $proposal->getType()) {
                $action = $this->actionRepository->findOneBy(['name' => 'carpool_ad_posted']);
                $actionEvent = new ActionEvent($action, $proposal->getUser());
                $actionEvent->setProposal($proposal);
                $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
            }
        }

        // dispatch en event
        $event = new ProposalPostedEvent($proposal);
        $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);

        //     // dispatch en event
        //     // todo determine the right matching to send
        //     if ($sendEvent && !is_null($matchingForEvent)) {
        //         $event = new MatchingNewEvent($matchingForEvent, $proposal->getUser());
        //         $this->eventDispatcher->dispatch(MatchingNewEvent::NAME, $event);
        //     }
        //     // dispatch en event who is not sent
        //     // $event = new ProposalPostedEvent($proposal);
        //     // $this->eventDispatcher->dispatch(ProposalPostedEvent::NAME, $event);
        // }

        return $proposal;
    }

    /**
     * @return Response
     *
     * @throws \Exception
     */
    public function deleteProposal(Proposal $proposal, ?array $body = null)
    {
        $asks = $this->askManager->getAsksFromProposal($proposal);
        if (count($asks) > 0) {
            /** @var Ask $ask */
            foreach ($asks as $ask) {
                // todo : find why class of $ask can be a proxy of Ask class
                if (Ask::class !== get_class($ask)) {
                    continue;
                }

                $deleter = ($body['deleterId'] == $ask->getUser()->getId()) ? $ask->getUser() : $ask->getUserRelated();
                $recipient = ($body['deleterId'] == $ask->getUser()->getId()) ? $ask->getUserRelated() : $ask->getUser();
                if (isset($body['deletionMessage']) && '' != $body['deletionMessage']) {
                    $message = $this->internalMessageManager->createMessage($deleter, [$recipient], $body['deletionMessage'], null, null);
                    $this->entityManager->persist($message);
                }

                $now = new \DateTime();
                // Ask user is driver
                if (($this->askManager->isAskUserDriver($ask) && ($ask->getUser()->getId() == $deleter->getId())) || ($this->askManager->isAskUserPassenger($ask) && ($ask->getUserRelated()->getId() == $deleter->getId()))) {
                    // TO DO check if the deletion is just before 24h and in that case send an other email
                    // /** @var Criteria $criteria */
                    $criteria = $ask->getMatching()->getProposalOffer()->getCriteria();
                    $askDateTime = $criteria->getFromTime() ?
                        new \DateTime($criteria->getFromDate()->format('Y-m-d').' '.$criteria->getFromTime()->format('H:i:s')) :
                        new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                    // Accepted
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() or Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()) {
                        if ($askDateTime->getTimestamp() - $now->getTimestamp() > 24 * 60 * 60) {
                            $event = new DriverAskAdDeletedEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new DriverAskAdDeletedUrgentEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(DriverAskAdDeletedUrgentEvent::NAME, $event);
                        }
                    } elseif (Ask::STATUS_PENDING_AS_DRIVER == $ask->getStatus() or Ask::STATUS_PENDING_AS_PASSENGER == $ask->getStatus()) {
                        $event = new AskAdDeletedEvent($ask, $deleter->getId());
                        $this->eventDispatcher->dispatch(AskAdDeletedEvent::NAME, $event);
                    }
                // Ask user is passenger
                } elseif (($this->askManager->isAskUserPassenger($ask) && ($ask->getUser()->getId() == $deleter->getId())) || ($this->askManager->isAskUserDriver($ask) && ($ask->getUserRelated()->getId() == $deleter->getId()))) {
                    // TO DO check if the deletion is just before 24h and in that case send an other email
                    // /** @var Criteria $criteria */
                    $criteria = $ask->getMatching()->getProposalRequest()->getCriteria();
                    $askDateTime = $criteria->getFromTime() ?
                        new \DateTime($criteria->getFromDate()->format('Y-m-d').' '.$criteria->getFromTime()->format('H:i:s')) :
                        new \DateTime($criteria->getFromDate()->format('Y-m-d H:i:s'));

                    // Accepted
                    if (Ask::STATUS_ACCEPTED_AS_DRIVER == $ask->getStatus() or Ask::STATUS_ACCEPTED_AS_PASSENGER == $ask->getStatus()) {
                        // If ad is in more than 24h
                        if ($askDateTime->getTimestamp() - $now->getTimestamp() > 24 * 60 * 60) {
                            $event = new PassengerAskAdDeletedEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedEvent::NAME, $event);
                        } else {
                            $event = new PassengerAskAdDeletedUrgentEvent($ask, $deleter->getId());
                            $this->eventDispatcher->dispatch(PassengerAskAdDeletedUrgentEvent::NAME, $event);
                        }
                    } elseif (Ask::STATUS_PENDING_AS_DRIVER == $ask->getStatus() or Ask::STATUS_PENDING_AS_PASSENGER == $ask->getStatus()) {
                        $event = new AskAdDeletedEvent($ask, $deleter->getId());
                        $this->eventDispatcher->dispatch(AskAdDeletedEvent::NAME, $event);
                    }
                }
            }
        }

        $this->entityManager->remove($proposal);
        $this->entityManager->flush();

        return new Response(204, 'Deleted with success');
    }

    // DYNAMIC

    /**
     * Check if a user has a pending dynamic ad.
     *
     * @param User $user The user
     *
     * @return bool
     */
    public function hasPendingDynamic(User $user)
    {
        return count($this->proposalRepository->findBy(['user' => $user, 'dynamic' => true, 'active' => true])) > 0;
    }

    /**
     * Update matchings for a proposal.
     *
     * @param Proposal $proposal The proposal to treat
     * @param Address  $address  The current address
     *
     * @return Proposal The treated proposal
     */
    public function updateMatchingsForProposal(Proposal $proposal, Address $address)
    {
        // set the directions
        $proposal = $this->updateDirection($proposal, $address);

        // matching analyze, but exclude the inactive proposals : can happen after an ask from a passenger to a driver
        if ($proposal->isActive()) {
            $proposal = $this->proposalMatcher->updateMatchingsForProposal($proposal);
        }

        return $proposal;
    }

    // MASS

    /**
     * Set the directions and default values for imported users proposals and criterias.
     *
     * @param int $batch The batch size
     */
    public function setDirectionsAndDefaultsForImport(int $batch)
    {
        $this->logger->info('Start setDirectionsAndDefaultsForImport | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        // we search the criterias that need calculation
        $criteriasFound = $this->criteriaRepository->findByUserImportStatus(UserImport::STATUS_USER_TREATED, new \DateTime());
        $this->setDirectionsAndDefaultsForCriterias($criteriasFound, $batch);
        $this->logger->info('End setDirectionsAndDefaultsForImport | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Set the directions and default values for all criterias.
     * Used for fixtures.
     *
     * @param int $batch The batch size
     */
    public function setDirectionsAndDefaultsForAllCriterias(int $batch)
    {
        $this->logger->info('Start setDirectionsAndDefaults | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        // we search the criterias that need calculation
        $criteriasFound = $this->criteriaRepository->findAllForDirectionsAndDefault();
        $this->setDirectionsAndDefaultsForCriterias($criteriasFound, $batch);
        $this->logger->info('End setDirectionsAndDefaults | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Create matchings for all proposals at once.
     */
    public function createMatchingsForAllProposals()
    {
        // we create an array of all proposals without matchings to treat
        $proposalIds = $this->proposalRepository->findAllValidWithoutMatchingsProposalIds();
        $this->logger->info('Start creating candidates | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $this->proposalMatcher->findPotentialMatchingsForProposals($proposalIds, false);
        $this->logger->info('End creating candidates | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        // treat the return and opposite
        $this->createLinkedAndOppositesForProposals($proposalIds);
    }

    /**
     * Create matchings for multiple proposals at once.
     *
     * @param array $proposals The proposals to treat
     *
     * @return array The proposals treated
     */
    public function createMatchingsForProposals(array $proposalIds)
    {
        // 1 - make an array of all potential matching proposals for each proposal
        // findPotentialMatchingsForProposals :
        // $potentialProposals = [
        //     'proposalID' => [
        //         'proposal1',
        //         'proposal2',
        //         ...
        //     ]
        // ];

        // 2 - make an array of candidates as driver and passenger
        // $candidatesProposals = [
        //     'proposalID' => [
        //         'candidateDrivers' => [
        //         ],
        //         'candidatePassengers' => [
        //         ]
        //     ]
        // ];

        $this->logger->info('Start creating candidates | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $this->proposalMatcher->findPotentialMatchingsForProposals($proposalIds);
        $this->logger->info('End creating candidates | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $proposalIds;
    }

    /**
     * Create linked and opposite matchings for multiple proposals at once.
     *
     * @param array $proposals The proposals to treat
     *
     * @return array The proposals treated
     */
    public function createLinkedAndOppositesForProposals(array $proposals)
    {
        foreach ($proposals as $proposalId) {
            $proposal = $this->proposalRepository->find($proposalId['id']);
            // if the proposal is a round trip, we want to link the potential matching results
            if (Proposal::TYPE_OUTWARD == $proposal->getType()) {
                $this->matchingRepository->linkRelatedMatchings($proposalId['id']);
            }
            // if the requester can be driver and passenger, we want to link the potential opposite matching results
            if ($proposal->getCriteria()->isDriver() && $proposal->getCriteria()->isPassenger()) {
                // linking for the outward
                $this->matchingRepository->linkOppositeMatchings($proposalId['id']);
                if (Proposal::TYPE_OUTWARD == $proposal->getType()) {
                    // linking for the return
                    $this->matchingRepository->linkOppositeMatchings($proposal->getProposalLinked()->getId());
                }
            }
        }
    }

    public function removeOutdatedExternalSearches(?int $numberOfDays = null)
    {
        if (file_exists($this->params['batchTemp'].self::CHECK_OUTDATED_SEARCHES_RUNNING_FILE)) {
            $this->logger->info('Remove outdated searches already running | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            return false;
        }

        $this->logger->info('Start removing outdated external searches | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        set_time_limit(self::OUTDATED_SEARCHES_EXECUTION_LIMIT_IN_SECONDS);
        ini_set('memory_limit', self::OUTDATED_SEARCHES_MEMORY_LIMIT_IN_MO.'M');

        $fp = fopen($this->params['batchTemp'].self::CHECK_OUTDATED_SEARCHES_RUNNING_FILE, 'w');
        fwrite($fp, '+');

        if (is_null($numberOfDays)) {
            $numberOfDays = self::OUTDATED_SEARCHES_AFTER_DAYS;
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('P'.$numberOfDays.'D'));

        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->entityManager->getConnection()->prepare(
            'CREATE TEMPORARY TABLE outdated_proposals (
            id int NOT NULL,
            PRIMARY KEY(id));
        '
        )->execute()
            && $this->entityManager->getConnection()->prepare(
                "INSERT INTO outdated_proposals (id)
            (SELECT DISTINCT proposal.id FROM proposal
            LEFT JOIN matching m1 ON m1.proposal_offer_id = proposal.id
            LEFT JOIN matching m2 ON m2.proposal_request_id = proposal.id
            LEFT JOIN ask a1 ON a1.matching_id = m1.id
            LEFT JOIN ask a2 ON a2.matching_id = m2.id
            WHERE
            proposal.private = 1 AND
            proposal.external_id IS NOT NULL AND
            proposal.created_date <= '".$date->format('Y-m-d')."' AND
            (m1.id IS NULL OR a1.id IS NULL) AND
            (m2.id IS NULL OR a2.id IS NULL));
            "
            )->execute()
        && $this->entityManager->getConnection()->prepare('start transaction;')->execute()
        && $this->entityManager->getConnection()->prepare('DELETE FROM proposal WHERE id in (select id from outdated_proposals);')->execute()
        && $this->entityManager->getConnection()->prepare('commit;')->execute()
        && $this->entityManager->getConnection()->prepare('DROP TABLE outdated_proposals;')->execute();

        fclose($fp);
        unlink($this->params['batchTemp'].self::CHECK_OUTDATED_SEARCHES_RUNNING_FILE);

        $this->logger->info('End removing outdated external searches | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $this->removeOrphans();
    }

    public function removeOrphans()
    {
        if (file_exists($this->params['batchTemp'].self::CHECK_REMOVE_ORPHANS_RUNNING_FILE)) {
            $this->logger->info('Remove orphans already running | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            return false;
        }

        $this->logger->info('Start removing carpool orphans | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        set_time_limit(self::REMOVE_ORPHANS_EXECUTION_LIMIT_IN_SECONDS);
        ini_set('memory_limit', self::REMOVE_ORPHANS_MEMORY_LIMIT_IN_MO.'M');

        $fp = fopen($this->params['batchTemp'].self::CHECK_REMOVE_ORPHANS_RUNNING_FILE, 'w');
        fwrite($fp, '+');

        $result = $this->removeOrphanCriteria() && $this->removeOrphanAddresses() && $this->removeOrphanDirections();

        fclose($fp);
        unlink($this->params['batchTemp'].self::CHECK_REMOVE_ORPHANS_RUNNING_FILE);

        $this->logger->info('End removing carpool orphans | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }

    public function optimizeCarpoolRelatedTables()
    {
        $this->logger->info('Start optimizing carpool related tables | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        set_time_limit(self::OPTIMIZE_EXECUTION_LIMIT_IN_SECONDS);
        ini_set('memory_limit', self::OPTIMIZE_MEMORY_LIMIT_IN_MO.'M');
        $result = $this->entityManager->getConnection()->prepare('OPTIMIZE TABLE proposal, criteria, matching, waypoint, address, address_territory, direction, direction_territory;')->execute();
        $this->logger->info('End optimizing carpool related tables | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $result;
    }

    public function cleanUserOrphanProposals(User $user)
    {
        $orphanProposals = $this->proposalRepository->findUserOrphanProposals($user);
        foreach ($orphanProposals as $orphanProposal) {
            $this->entityManager->remove($orphanProposal);
        }
        $this->entityManager->flush();
    }

    public function sendCarpoolAdRenewal(?int $numberOfDays = null)
    {
        $proposals = $this->proposalRepository->findProposalsOutdated($numberOfDays);

        foreach ($proposals as $proposal) {
            $event = new AdRenewalEvent($proposal);
            $this->eventDispatcher->dispatch(AdRenewalEvent::NAME, $event);
        }
    }

    public function homogenizeRegularProposalsWithLocalityOnly(): int
    {
        $addresses = $this->getActiveRegularProposalsWithLocalityOnly();
        $this->logger->info('Number of addresses to check : '.count($addresses));
        $addressesToRecode = $this->getActiveRegularProposalAddressesToRecode($addresses);

        return $this->recodeActiveRegularProposalAddresses($addressesToRecode);
    }

    private function getActiveRegularProposalsWithLocalityOnly(): array
    {
        $stmt_origin = $this->entityManager->getConnection()->prepare(
            'SELECT
                a.address_locality,
                a.longitude,
                a.latitude
            FROM proposal p
            LEFT JOIN criteria c ON c.id = p.criteria_id
            LEFT JOIN waypoint w ON w.proposal_id = p.id
            LEFT JOIN address a ON a.id = w.address_id
            WHERE
                (p.private IS NULL OR p.private = 0) AND
                c.frequency > 1 AND
                c.to_date IS NOT NULL AND c.to_date>=NOW() AND
                w.position = 0 AND
                a.address_locality IS NOT NULL AND a.address_locality != "" AND
                (a.street_address IS NULL OR a.street_address = "") AND
                (a.postal_code IS NULL OR a.postal_code = "") AND
                (a.house_number IS NULL OR a.house_number = "") AND
                (a.street IS NULL OR a.street = "")
            GROUP BY
                address_locality,
                longitude,
                latitude
            '
        );
        $stmt_origin->execute();
        $addresses_origin = $stmt_origin->fetchAll();

        $stmt_destination = $this->entityManager->getConnection()->prepare(
            'SELECT
                a.address_locality,
                a.longitude,
                a.latitude
            FROM proposal p
            LEFT JOIN criteria c ON c.id = p.criteria_id
            LEFT JOIN waypoint w ON w.proposal_id = p.id
            LEFT JOIN address a ON a.id = w.address_id
            WHERE
                (p.private IS NULL OR p.private = 0) AND
                c.frequency > 1 AND
                c.to_date IS NOT NULL AND c.to_date>=NOW() AND
                w.destination = 1 AND
                a.address_locality IS NOT NULL AND a.address_locality != "" AND
                (a.street_address IS NULL OR a.street_address = "") AND
                (a.postal_code IS NULL OR a.postal_code = "") AND
                (a.house_number IS NULL OR a.house_number = "") AND
                (a.street IS NULL OR a.street = "")
            GROUP BY
                address_locality,
                longitude,
                latitude
            '
        );
        $stmt_destination->execute();
        $addresses_destination = $stmt_destination->fetchAll();

        return array_merge($addresses_origin, $addresses_destination);
    }

    private function getActiveRegularProposalAddressesToRecode(array $addresses): array
    {
        $this->mobicoopGeocoderPointProvider->setExclusionTypes(['venue', 'street', 'housenumber']);
        $this->mobicoopGeocoderPointProvider->setMaxResults(1);
        $recoded = [];
        $i = 0;
        foreach ($addresses as $address) {
            ++$i;
            if (($i % 100) == 0) {
                $this->logger->info($i.' addresses checked');
            }
            $points = $this->mobicoopGeocoderPointProvider->search($address['address_locality']);
            if (
                count($points) > 0
                && (
                    (((float) $address['latitude']) != $points[0]->getLat())
                    || (((float) $address['longitude']) != $points[0]->getLon())
                )
                && $this->geoTools->haversineGreatCircleDistance(
                    $points[0]->getLat(),
                    $points[0]->getLon(),
                    $address['latitude'],
                    $address['longitude']
                ) <= self::HOMOGENIZE_REGULAR_PROPOSAL_ADDRESS_DISTANCE
            ) {
                $recoded[] = [
                    'locality' => $points[0]->getLocality(),
                    'lat' => $points[0]->getLat(),
                    'lon' => $points[0]->getLon(),
                    'olocality' => $address['address_locality'],
                    'olat' => $address['latitude'],
                    'olon' => $address['longitude'],
                ];
            }
        }

        return $recoded;
    }

    private function recodeActiveRegularProposalAddresses(array $addressesToRecode): bool
    {
        if (count($addressesToRecode) > 0) {
            $this->entityManager->getConnection()->prepare('start transaction;')->execute();
            $i = 0;
            foreach ($addressesToRecode as $recode) {
                ++$i;
                if (($i % 100) == 0) {
                    $this->logger->info($i.' addresses updated');
                }
                if (!$this->entityManager->getConnection()->prepare(
                    '
                    UPDATE
                        address
                    SET
                        longitude='.$recode['lon'].',
                        latitude='.$recode['lat'].',
                        address_locality="'.$recode['locality'].'",
                        geo_json=PointFromText(\'POINT('.$recode['lon'].' '.$recode['lat'].')\')
                    WHERE
                        address_locality="'.$recode['olocality'].'" AND
                        latitude='.$recode['olat'].' AND
                        longitude='.$recode['olon']
                )->execute()) {
                    return false;
                }
            }
            $this->entityManager->getConnection()->prepare('commit;')->execute();
        }

        return true;
    }

    /**
     * Set default parameters for a proposal.
     *
     * @param Proposal $proposal The proposal
     *
     * @return Proposal The proposal treated
     */
    private function setDefaults(Proposal $proposal)
    {
        $this->logger->info('ProposalManager : setDefaults '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        if (is_null($proposal->getCriteria()->getAnyRouteAsPassenger())) {
            $proposal->getCriteria()->setAnyRouteAsPassenger($this->params['defaultAnyRouteAsPassenger']);
        }
        if (is_null($proposal->getCriteria()->isStrictDate())) {
            $proposal->getCriteria()->setStrictDate($this->params['defaultStrictDate']);
        }
        if (is_null($proposal->getCriteria()->getPriceKm())) {
            $proposal->getCriteria()->setPriceKm($this->params['defaultPriceKm']);
        }
        if (Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency()) {
            if (is_null($proposal->getCriteria()->isStrictPunctual())) {
                $proposal->getCriteria()->setStrictPunctual($this->params['defaultStrictPunctual']);
            }
            if (is_null($proposal->getCriteria()->getMarginDuration())) {
                $proposal->getCriteria()->setMarginDuration($this->params['defaultMarginDuration']);
            }
        } else {
            if (is_null($proposal->getCriteria()->isStrictRegular())) {
                $proposal->getCriteria()->setStrictRegular($this->params['defaultStrictRegular']);
            }
            if (is_null($proposal->getCriteria()->getMonMarginDuration())) {
                $proposal->getCriteria()->setMonMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getTueMarginDuration())) {
                $proposal->getCriteria()->setTueMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getWedMarginDuration())) {
                $proposal->getCriteria()->setWedMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getThuMarginDuration())) {
                $proposal->getCriteria()->setThuMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getFriMarginDuration())) {
                $proposal->getCriteria()->setFriMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getSatMarginDuration())) {
                $proposal->getCriteria()->setSatMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getSunMarginDuration())) {
                $proposal->getCriteria()->setSunMarginDuration($this->params['defaultMarginDuration']);
            }
            if (is_null($proposal->getCriteria()->getToDate())) {
                // end date is usually null, except when creating a proposal after a matching search
                $endDate = clone $proposal->getCriteria()->getFromDate();
                // the date can be immutable
                $toDate = $endDate->add(new \DateInterval('P'.$this->params['defaultRegularLifeTime'].'Y'));
                $proposal->getCriteria()->setToDate($toDate);
            }
        }

        return $proposal;
    }

    /**
     * Calculation of min and max times.
     * We calculate the min and max times only if the time is set (it could be not set for a simple search).
     *
     * @param Proposal $proposal The proposal
     *
     * @return Proposal The proposal treated
     */
    private function setMinMax(Proposal $proposal)
    {
        if (Criteria::FREQUENCY_PUNCTUAL == $proposal->getCriteria()->getFrequency() && $proposal->getCriteria()->getFromTime()) {
            list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFromTime(), $proposal->getCriteria()->getMarginDuration());
            $proposal->getCriteria()->setMinTime($minTime);
            $proposal->getCriteria()->setMaxTime($maxTime);
        } else {
            if ($proposal->getCriteria()->isMonCheck() && $proposal->getCriteria()->getMonTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getMonTime(), $proposal->getCriteria()->getMonMarginDuration());
                $proposal->getCriteria()->setMonMinTime($minTime);
                $proposal->getCriteria()->setMonMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isTueCheck() && $proposal->getCriteria()->getTueTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getTueTime(), $proposal->getCriteria()->getTueMarginDuration());
                $proposal->getCriteria()->setTueMinTime($minTime);
                $proposal->getCriteria()->setTueMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isWedCheck() && $proposal->getCriteria()->getWedTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getWedTime(), $proposal->getCriteria()->getWedMarginDuration());
                $proposal->getCriteria()->setWedMinTime($minTime);
                $proposal->getCriteria()->setWedMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isThuCheck() && $proposal->getCriteria()->getThuTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getThuTime(), $proposal->getCriteria()->getThuMarginDuration());
                $proposal->getCriteria()->setThuMinTime($minTime);
                $proposal->getCriteria()->setThuMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isFriCheck() && $proposal->getCriteria()->getFriTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getFriTime(), $proposal->getCriteria()->getFriMarginDuration());
                $proposal->getCriteria()->setFriMinTime($minTime);
                $proposal->getCriteria()->setFriMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSatCheck() && $proposal->getCriteria()->getSatTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSatTime(), $proposal->getCriteria()->getSatMarginDuration());
                $proposal->getCriteria()->setSatMinTime($minTime);
                $proposal->getCriteria()->setSatMaxTime($maxTime);
            }
            if ($proposal->getCriteria()->isSunCheck() && $proposal->getCriteria()->getSunTime()) {
                list($minTime, $maxTime) = self::getMinMaxTime($proposal->getCriteria()->getSunTime(), $proposal->getCriteria()->getSunMarginDuration());
                $proposal->getCriteria()->setSunMinTime($minTime);
                $proposal->getCriteria()->setSunMaxTime($maxTime);
            }
        }

        return $proposal;
    }

    /**
     * Set the directions for a proposal.
     *
     * @param Proposal $proposal The proposal
     *
     * @return Proposal The proposal treated
     */
    private function setDirections(Proposal $proposal)
    {
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            if (!$waypoint->isReached()) {
                $addresses[] = $waypoint->getAddress();
            }
        }
        $routes = null;
        $direction = null;
        if ($proposal->getCriteria()->isDriver()) {
            if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                // for now we only keep the first route !
                // if we ever want alternative routes we should pass the route as parameter of this method
                // (problem : the route has no id, we should pass the whole route to check which route is chosen by the user...
                //      => we would have to think of a way to simplify...)
                if (($direction = $routes[0]) == null) {
                    throw new AdException(AdException::WRONG_COORDINATES);
                }
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->setDirectionDriver($direction);
                $proposal->getCriteria()->setMaxDetourDistance($direction->getDistance() * $this->proposalMatcher::getMaxDetourDistancePercent() / 100);
                $proposal->getCriteria()->setMaxDetourDuration($direction->getDuration() * $this->proposalMatcher::getMaxDetourDurationPercent() / 100);
            }
        }
        if ($proposal->getCriteria()->isPassenger()) {
            if ($routes && count($addresses) > 2) {
                // if the user is passenger we keep only the first and last points
                if ($routes = $this->geoRouter->getRoutes([$addresses[0], $addresses[count($addresses) - 1]], false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            } elseif (!$routes) {
                if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            }
            if ($direction) {
                if (is_null($direction->getBboxMinLon()) && is_null($direction->getBboxMinLat()) && is_null($direction->getBboxMaxLon()) && is_null($direction->getBboxMaxLat())) {
                    $direction->setBboxMaxLat($addresses[0]->getLatitude());
                    $direction->setBboxMaxLon($addresses[0]->getLongitude());
                    $direction->setBboxMinLat($addresses[0]->getLatitude());
                    $direction->setBboxMinLon($addresses[0]->getLongitude());
                }
                if ($routes) {
                    $direction->setAutoGeoJsonDetail();
                    $proposal->getCriteria()->setDirectionPassenger($direction);
                }
            } else {
                throw new AdException(AdException::WRONG_COORDINATES);
            }
        }

        return $proposal;
    }

    /**
     * Set the prices for a proposal.
     *
     * @param Proposal $proposal The proposal
     *
     * @return Proposal The proposal treated
     */
    private function setPrices(Proposal $proposal)
    {
        if ($proposal->getCriteria()->getDirectionDriver()) {
            $proposal->getCriteria()->setDriverComputedPrice((string) ((int) $proposal->getCriteria()->getDirectionDriver()->getDistance() * (float) $proposal->getCriteria()->getPriceKm() / 1000));
            $proposal->getCriteria()->setDriverComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $proposal->getCriteria()->getDriverComputedPrice(), $proposal->getCriteria()->getFrequency()));
        }
        if ($proposal->getCriteria()->getDirectionPassenger()) {
            $proposal->getCriteria()->setPassengerComputedPrice((string) ((int) $proposal->getCriteria()->getDirectionPassenger()->getDistance() * (float) $proposal->getCriteria()->getPriceKm() / 1000));
            $proposal->getCriteria()->setPassengerComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $proposal->getCriteria()->getPassengerComputedPrice(), $proposal->getCriteria()->getFrequency()));
        }

        return $proposal;
    }

    /**
     * Update the direction of a proposal, using the given address as origin.
     * Used for dynamic carpooling, to compute the remaining direction to the destination.
     * This kind of proposal should only have one role, but we will compute both eventually.
     *
     * @param Proposal $proposal The proposal
     * @param Address  $address  The current address
     *
     * @return Proposal The proposal with its updated direction
     */
    private function updateDirection(Proposal $proposal, Address $address)
    {
        // the first point is the current address
        $addresses = [$address];
        foreach ($proposal->getWaypoints() as $waypoint) {
            // we take all the waypoints but the first and the reached
            if (!$waypoint->isReached() && $waypoint->getPosition() > 0) {
                $addresses[] = $waypoint->getAddress();
            }
        }
        $routes = null;
        if ($proposal->getCriteria()->isDriver()) {
            if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                // we update only some of the properties : distance, duration, ascend, descend, detail, format, snapped
                // bearing and bbox are not updated as they are computed for the whole original direction
                // (the current direction of the driver could not match with the passenger direction, whereas the whole directions could match)
                $direction = $routes[0];
                $direction->setSaveGeoJson(true);
                $direction->setDetailUpdatable(true);
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->getDirectionDriver()->setDistance($direction->getDistance());
                $proposal->getCriteria()->getDirectionDriver()->setDuration($direction->getDuration());
                $proposal->getCriteria()->getDirectionDriver()->setAscend($direction->getAscend());
                $proposal->getCriteria()->getDirectionDriver()->setDescend($direction->getDescend());
                // $proposal->getCriteria()->getDirectionDriver()->setDetail($direction->getDetail());
                $proposal->getCriteria()->getDirectionDriver()->setFormat($direction->getFormat());
                $proposal->getCriteria()->getDirectionDriver()->setSnapped($direction->getSnapped());
                $proposal->getCriteria()->getDirectionDriver()->setGeoJsonDetail($direction->getGeoJsonDetail());
                $proposal->getCriteria()->getDirectionDriver()->setGeoJsonSimplified($direction->getGeoJsonSimplified());
            }
        }
        if ($proposal->getCriteria()->isPassenger()) {
            if ($routes && count($addresses) > 2) {
                // if the user is passenger we keep only the first and last points
                if ($routes = $this->geoRouter->getRoutes([$addresses[0], $addresses[count($addresses) - 1]], false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            } elseif (!$routes) {
                if ($routes = $this->geoRouter->getRoutes($addresses, false, false, GeorouterInterface::RETURN_TYPE_OBJECT)) {
                    $direction = $routes[0];
                }
            }
            if ($routes) {
                $direction->setSaveGeoJson(true);
                $direction->setDetailUpdatable(true);
                $direction->setAutoGeoJsonDetail();
                $proposal->getCriteria()->getDirectionPassenger()->setDistance($direction->getDistance());
                $proposal->getCriteria()->getDirectionPassenger()->setDuration($direction->getDuration());
                $proposal->getCriteria()->getDirectionPassenger()->setAscend($direction->getAscend());
                $proposal->getCriteria()->getDirectionPassenger()->setDescend($direction->getDescend());
                // $proposal->getCriteria()->getDirectionPassenger()->setDetail($direction->getDetail());
                $proposal->getCriteria()->getDirectionPassenger()->setFormat($direction->getFormat());
                $proposal->getCriteria()->getDirectionPassenger()->setSnapped($direction->getSnapped());
                $proposal->getCriteria()->getDirectionPassenger()->setGeoJsonDetail($direction->getGeoJsonDetail());
                $proposal->getCriteria()->getDirectionPassenger()->setGeoJsonSimplified($direction->getGeoJsonSimplified());
            }
        }

        return $proposal;
    }

    /**
     * Set the directions and default values for given criterias.
     *
     * @param array $criterias The criterias to look for
     * @param int   $batch     The batch size
     */
    private function setDirectionsAndDefaultsForCriterias(array $criterias, int $batch)
    {
        gc_enable();

        $addressesForRoutes = [];
        $owner = [];
        $ids = [];

        $i = 0;

        $this->logger->info('setDirectionsAndDefaultsForCriterias | Start iterate at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $criteriasTreated = [];
        foreach ($criterias as $key => $criteria) {
            if (!array_key_exists($criteria['cid'], $criteriasTreated)) {
                $criteriasTreated[$criteria['cid']] = [
                    'cid' => $criteria['cid'],
                    'driver' => $criteria['driver'],
                    'passenger' => $criteria['passenger'],
                    'addresses' => [
                        [
                            'position' => $criteria['position'],
                            'destination' => $criteria['destination'],
                            'latitude' => $criteria['latitude'],
                            'longitude' => $criteria['longitude'],
                        ],
                    ],
                ];
            } else {
                $element = [
                    'position' => $criteria['position'],
                    'destination' => $criteria['destination'],
                    'latitude' => $criteria['latitude'],
                    'longitude' => $criteria['longitude'],
                ];
                if (!in_array($element, $criteriasTreated[$criteria['cid']]['addresses'])) {
                    $criteriasTreated[$criteria['cid']]['addresses'][] = $element;
                }
            }
        }

        foreach ($criteriasTreated as $criteria) {
            $addressesDriver = [];
            $addressesPassenger = [];
            foreach ($criteria['addresses'] as $waypoint) {
                // waypoints are already retrieved ordered by position, no need to check the position here
                if ($criteria['driver']) {
                    $address = new Address();
                    $address->setLatitude($waypoint['latitude']);
                    $address->setLongitude($waypoint['longitude']);
                    $addressesDriver[] = $address;
                }
                if ($criteria['passenger'] && (0 == $waypoint['position'] || $waypoint['destination'])) {
                    $address = new Address();
                    $address->setLatitude($waypoint['latitude']);
                    $address->setLongitude($waypoint['longitude']);
                    $addressesPassenger[] = $address;
                }
            }
            if (count($addressesDriver) > 0) {
                $addressesForRoutes[$i] = [$addressesDriver];
                $owner[$criteria['cid']]['driver'] = $i;
                ++$i;
            }
            if (count($addressesPassenger) > 0) {
                $addressesForRoutes[$i] = [$addressesPassenger];
                $owner[$criteria['cid']]['passenger'] = $i;
                ++$i;
            }
            $ids[] = $criteria['cid'];
        }
        $this->logger->info('setDirectionsAndDefaultsForCriterias | End iterate at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->logger->info('setDirectionsAndDefaultsForCriterias | Start get routes status '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_RAW);
        $this->logger->info('setDirectionsAndDefaultsForCriterias | End get routes status '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $criteriasTreated = null;
        unset($criteriasTreated);

        if (count($ids) > 0) {
            $qCriteria = $this->entityManager->createQuery('SELECT c from App\Carpool\Entity\Criteria c WHERE c.id IN ('.implode(',', $ids).')');

            $iterableResult = $qCriteria->iterate();
            $this->logger->info('Start treat rows '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $pool = 0;
            foreach ($iterableResult as $row) {
                $criteria = $row[0];
                // foreach ($criterias as $criteria) {
                if (isset($owner[$criteria->getId()]['driver'], $ownerRoutes[$owner[$criteria->getId()]['driver']])) {
                    $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['driver']][0]);
                    $direction->setSaveGeoJson(true);
                    $criteria->setDirectionDriver($direction);
                    $criteria->setMaxDetourDistance($direction->getDistance() * $this->proposalMatcher::getMaxDetourDistancePercent() / 100);
                    $criteria->setMaxDetourDuration($direction->getDuration() * $this->proposalMatcher::getMaxDetourDurationPercent() / 100);
                }
                if (isset($owner[$criteria->getId()]['passenger'], $ownerRoutes[$owner[$criteria->getId()]['passenger']])) {
                    $direction = $this->geoRouter->getRouter()->deserializeDirection($ownerRoutes[$owner[$criteria->getId()]['passenger']][0]);
                    $direction->setSaveGeoJson(true);
                    $criteria->setDirectionPassenger($direction);
                }

                if (is_null($criteria->getAnyRouteAsPassenger())) {
                    $criteria->setAnyRouteAsPassenger($this->params['defaultAnyRouteAsPassenger']);
                }
                if (is_null($criteria->isStrictDate())) {
                    $criteria->setStrictDate($this->params['defaultStrictDate']);
                }
                if (is_null($criteria->getPriceKm())) {
                    $criteria->setPriceKm($this->params['defaultPriceKm']);
                }
                if (Criteria::FREQUENCY_PUNCTUAL == $criteria->getFrequency()) {
                    if (is_null($criteria->isStrictPunctual())) {
                        $criteria->setStrictPunctual($this->params['defaultStrictPunctual']);
                    }
                    if (is_null($criteria->getMarginDuration())) {
                        $criteria->setMarginDuration($this->params['defaultMarginDuration']);
                    }
                } else {
                    if (is_null($criteria->isStrictRegular())) {
                        $criteria->setStrictRegular($this->params['defaultStrictRegular']);
                    }
                    if (is_null($criteria->getMonMarginDuration())) {
                        $criteria->setMonMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getTueMarginDuration())) {
                        $criteria->setTueMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getWedMarginDuration())) {
                        $criteria->setWedMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getThuMarginDuration())) {
                        $criteria->setThuMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getFriMarginDuration())) {
                        $criteria->setFriMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getSatMarginDuration())) {
                        $criteria->setSatMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getSunMarginDuration())) {
                        $criteria->setSunMarginDuration($this->params['defaultMarginDuration']);
                    }
                    if (is_null($criteria->getToDate())) {
                        // end date is usually null, except when creating a proposal after a matching search
                        $endDate = clone $criteria->getFromDate();
                        // the date can be immutable
                        $toDate = $endDate->add(new \DateInterval('P'.$this->params['defaultRegularLifeTime'].'Y'));
                        $criteria->setToDate($toDate);
                    }
                }

                if (Criteria::FREQUENCY_PUNCTUAL == $criteria->getFrequency() && $criteria->getFromTime()) {
                    list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFromTime(), $criteria->getMarginDuration());
                    $criteria->setMinTime($minTime);
                    $criteria->setMaxTime($maxTime);
                } else {
                    if ($criteria->isMonCheck() && $criteria->getMonTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getMonTime(), $criteria->getMonMarginDuration());
                        $criteria->setMonMinTime($minTime);
                        $criteria->setMonMaxTime($maxTime);
                    }
                    if ($criteria->isTueCheck() && $criteria->getTueTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getTueTime(), $criteria->getTueMarginDuration());
                        $criteria->setTueMinTime($minTime);
                        $criteria->setTueMaxTime($maxTime);
                    }
                    if ($criteria->isWedCheck() && $criteria->getWedTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getWedTime(), $criteria->getWedMarginDuration());
                        $criteria->setWedMinTime($minTime);
                        $criteria->setWedMaxTime($maxTime);
                    }
                    if ($criteria->isThuCheck() && $criteria->getThuTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getThuTime(), $criteria->getThuMarginDuration());
                        $criteria->setThuMinTime($minTime);
                        $criteria->setThuMaxTime($maxTime);
                    }
                    if ($criteria->isFriCheck() && $criteria->getFriTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getFriTime(), $criteria->getFriMarginDuration());
                        $criteria->setFriMinTime($minTime);
                        $criteria->setFriMaxTime($maxTime);
                    }
                    if ($criteria->isSatCheck() && $criteria->getSatTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSatTime(), $criteria->getSatMarginDuration());
                        $criteria->setSatMinTime($minTime);
                        $criteria->setSatMaxTime($maxTime);
                    }
                    if ($criteria->isSunCheck() && $criteria->getSunTime()) {
                        list($minTime, $maxTime) = self::getMinMaxTime($criteria->getSunTime(), $criteria->getSunMarginDuration());
                        $criteria->setSunMinTime($minTime);
                        $criteria->setSunMaxTime($maxTime);
                    }
                    if ($criteria->getDirectionDriver()) {
                        $criteria->setDriverComputedPrice((string) ((int) $criteria->getDirectionDriver()->getDistance() * (float) $criteria->getPriceKm() / 1000));
                        $criteria->setDriverComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $criteria->getDriverComputedPrice(), $criteria->getFrequency()));
                    }
                    if ($criteria->getDirectionPassenger()) {
                        $criteria->setPassengerComputedPrice((string) ((int) $criteria->getDirectionPassenger()->getDistance() * (float) $criteria->getPriceKm() / 1000));
                        $criteria->setPassengerComputedRoundedPrice((string) $this->formatDataManager->roundPrice((float) $criteria->getPassengerComputedPrice(), $criteria->getFrequency()));
                    }
                }

                // batch
                ++$pool;
                if ($pool >= $batch) {
                    $this->logger->info('Batch '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    gc_collect_cycles();
                    $pool = 0;
                }
            }

            $this->logger->info('Stop treat rows '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            // final flush for pending persists
            if ($pool > 0) {
                $this->logger->info('Start final flush '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                $this->entityManager->flush();
                $this->logger->info('Start clear '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                $this->entityManager->clear();
                gc_collect_cycles();
                $this->logger->info('End flush clear '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            }
        }

        $this->logger->info('End update status '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    // returns the min and max time from a time and a margin
    private static function getMinMaxTime($time, $margin)
    {
        $minTime = clone $time;
        $maxTime = clone $time;
        $minTime->sub(new \DateInterval('PT'.$margin.'S'));
        if ($minTime->format('j') != $time->format('j')) {
            // the day has changed => we keep '00:00' as min time
            $minTime = new \DateTime('00:00:00');
        }
        $maxTime->add(new \DateInterval('PT'.$margin.'S'));
        if ($maxTime->format('j') != $time->format('j')) {
            // the day has changed => we keep '23:59:00' as max time
            $maxTime = new \DateTime('23:59:00');
        }

        return [
            $minTime,
            $maxTime,
        ];
    }

    private function removeOrphanCriteria()
    {
        return
            $this->entityManager->getConnection()->prepare(
                'CREATE TEMPORARY TABLE outdated_criteria (
                id int NOT NULL,
                PRIMARY KEY(id));
            '
            )->execute()
            && $this->entityManager->getConnection()->prepare(
                'INSERT INTO outdated_criteria (id)
            (SELECT criteria.id FROM criteria
            LEFT JOIN ask ON ask.criteria_id = criteria.id
            LEFT JOIN matching ON matching.criteria_id = criteria.id
            LEFT JOIN proposal ON proposal.criteria_id = criteria.id
            LEFT JOIN solidary_ask ON solidary_ask.criteria_id = criteria.id
            LEFT JOIN solidary_matching ON solidary_matching.criteria_id = criteria.id
            WHERE
            ask.criteria_id IS NULL AND
            matching.criteria_id IS NULL AND
            proposal.criteria_id IS NULL AND
            solidary_ask.criteria_id IS NULL AND
            solidary_matching.criteria_id IS NULL);
            '
            )->execute()
        && $this->entityManager->getConnection()->prepare('start transaction;')->execute()
        && $this->entityManager->getConnection()->prepare('DELETE FROM criteria WHERE id in (select id from outdated_criteria);')->execute()
        && $this->entityManager->getConnection()->prepare('commit;')->execute()
        && $this->entityManager->getConnection()->prepare('DROP TABLE outdated_criteria;')->execute();
    }

    private function removeOrphanAddresses()
    {
        return
            $this->entityManager->getConnection()->prepare(
                'CREATE TEMPORARY TABLE outdated_address (
                id int NOT NULL,
                PRIMARY KEY(id));
            '
            )->execute()
            && $this->entityManager->getConnection()->prepare(
                'INSERT INTO outdated_address (id)
                (SELECT address.id FROM address
                LEFT JOIN user ON address.user_id = user.id
                LEFT JOIN solidary_user ON solidary_user.address_id = address.id
                LEFT JOIN waypoint ON waypoint.address_id = address.id
                LEFT JOIN community ON community.address_id = address.id
                LEFT JOIN event ON event.address_id = address.id
                LEFT JOIN relay_point ON relay_point.address_id = address.id
                LEFT JOIN mass_person mp1 ON mp1.personal_address_id = address.id
                LEFT JOIN mass_person mp2 ON mp2.work_address_id = address.id
                LEFT JOIN carpool_proof cp1 ON cp1.pick_up_passenger_address_id = address.id
                LEFT JOIN carpool_proof cp2 ON cp2.pick_up_driver_address_id = address.id
                LEFT JOIN carpool_proof cp3 ON cp3.drop_off_passenger_address_id = address.id
                LEFT JOIN carpool_proof cp4 ON cp4.drop_off_driver_address_id = address.id
                LEFT JOIN carpool_proof cp5 ON cp5.origin_driver_address_id = address.id
                LEFT JOIN carpool_proof cp6 ON cp6.destination_driver_address_id = address.id
                WHERE
                    user.id IS NULL AND
                    solidary_user.id IS NULL AND
                    waypoint.id IS NULL AND
                    community.id IS NULL AND
                    event.id IS NULL AND
                    relay_point.id IS NULL AND
                    mp1.personal_address_id IS NULL AND
                    mp2.work_address_id IS NULL AND
                    cp1.pick_up_passenger_address_id IS NULL AND
                    cp2.pick_up_driver_address_id IS NULL AND
                    cp3.drop_off_passenger_address_id IS NULL AND
                    cp4.drop_off_driver_address_id IS NULL AND
                    cp5.origin_driver_address_id IS NULL AND
                    cp6.destination_driver_address_id IS NULL);
                '
            )->execute()
            && $this->entityManager->getConnection()->prepare('start transaction;')->execute()
            && $this->entityManager->getConnection()->prepare('DELETE FROM address WHERE id in (SELECT id FROM outdated_address);')->execute()
            && $this->entityManager->getConnection()->prepare('commit;')->execute()
            && $this->entityManager->getConnection()->prepare('DROP TABLE outdated_address;')->execute();
    }

    private function removeOrphanDirections()
    {
        return
            $this->entityManager->getConnection()->prepare(
                'CREATE TEMPORARY TABLE outdated_direction (
                id int NOT NULL,
                PRIMARY KEY(id));
            '
            )->execute()
            && $this->entityManager->getConnection()->prepare(
                'INSERT INTO outdated_direction (id)
                (SELECT direction.id FROM direction
                LEFT JOIN criteria c1 ON c1.direction_driver_id = direction.id
                LEFT JOIN criteria c2 ON c2.direction_passenger_id = direction.id
                LEFT JOIN position ON position.direction_id = direction.id
                LEFT JOIN carpool_proof ON carpool_proof.direction_id = direction.id
                WHERE
                c1.direction_driver_id IS NULL AND
                c2.direction_passenger_id IS NULL AND
                position.direction_id IS NULL AND
                carpool_proof.direction_id IS NULL);
                '
            )->execute()
            && $this->entityManager->getConnection()->prepare('start transaction;')->execute()
            && $this->entityManager->getConnection()->prepare('DELETE FROM direction WHERE id in (SELECT id FROM outdated_direction);')->execute()
            && $this->entityManager->getConnection()->prepare('commit;')->execute()
            && $this->entityManager->getConnection()->prepare('DROP TABLE outdated_direction;')->execute();
    }
}
