<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Repository;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Communication\Entity\Notified;
use App\Incentive\Resource\CeeSubscriptions;
use App\Incentive\Service\Validation\Validation;
use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class CarpoolProofRepository
{
    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;
    private $carpoolProofErroCheckLimit;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, int $carpoolProofErroCheckLimit)
    {
        $this->_userRepository = $userRepository;

        $this->repository = $entityManager->getRepository(CarpoolProof::class);
        $this->entityManager = $entityManager;
        $this->carpoolProofErroCheckLimit = $carpoolProofErroCheckLimit;
    }

    public function find(int $id): ?CarpoolProof
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Find a proof by ask and date.
     *
     * @param Ask       $ask  The ask
     * @param \DateTime $date The date
     *
     * @return null|CarpoolProof The carpool proof found or null if not found
     */
    public function findByAskAndDate(Ask $ask, \DateTime $date)
    {
        $startDate = clone $date;
        $startDate->setTime(0, 0);
        $endDate = clone $date;
        $endDate->setTime(23, 59, 59, 999);

        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.ask = :ask')
            ->andWhere('(cp.pickUpPassengerDate BETWEEN :startDate and :endDate) or (cp.pickUpDriverDate BETWEEN :startDate and :endDate)')
            ->setParameter('ask', $ask)
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
            ->setMaxResults(1)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * Find the remaining proofs for a user (driver or passenger) : used to find proofs related to a deleted ask.
     *
     * @param User $user The user
     *
     * @return null|CarpoolProof[] The carpool proofs found or null if not found
     */
    public function findRemainingByUser(User $user)
    {
        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.ask is null')
            ->andWhere('(cp.driver = :user or cp.passenger = :user)')
            ->setParameter('user', $user)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * Find proofs with given types and given period.
     *
     * @param array     $types     The possible types
     * @param \DateTime $startDate The start date of the period
     * @param \DateTime $endDate   The end date of the period
     * @param array     $status    The possible status
     *
     * @return CarpoolProof[] The carpool proofs found
     */
    public function findByTypesAndPeriod(array $types, \DateTime $startDate, \DateTime $endDate, array $status = null)
    {
        $startDate->setTime(0, 0);
        $endDate->setTime(23, 59, 59, 999);

        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.type in (:types)')
            ->andWhere('(cp.pickUpPassengerDate BETWEEN :startDate and :endDate) or (cp.pickUpDriverDate BETWEEN :startDate and :endDate)')
        ;

        if (!is_null($status)) {
            $query->andWhere('cp.status in (:status)')
                ->setParameter('status', $status)
            ;
        }

        $query
            ->setParameter('types', $types)
            ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'))
        ;

        return $query->getQuery()->getResult();
    }

    public function findCarpoolProofToCheck(array $status): ?array
    {
        $now = new \DateTime('now');
        $date = $now->modify('- '.$this->carpoolProofErroCheckLimit.'days')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('cp')
            ->where('cp.status in (:status)')
            ->orWhere('cp.status = :errorStatus AND cp.createdDate >= :date')
            ->setParameter('status', $status)
            ->setParameter('errorStatus', CarpoolProof::STATUS_ERROR)
            ->setParameter('date', $date)
        ;

        return $query->getQuery()->getResult();
    }

    /**
     * EEC query.
     */
    public function findCarpoolProofForEccRelaunch(User $driver, ?int $excludeId, array $allreadyDeaclaredJourneys, bool $isLongDistanceProcess = true): ?array
    {
        // TODO Vérifier que le trajet ne soit pas déjà une longue ou courte souscription
        $qb = $this->repository->createQueryBuilder('cp');

        $parameters = [
            'class' => CarpoolProof::TYPE_HIGH,
            'country' => Validation::REFERENCE_COUNTRY,
            'distance' => CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS,
            'driver' => $driver,
            'referenceDate' => \DateTime::createFromFormat('Y-m-d', Validation::REFERENCE_DATE),
            'status' => CarpoolProof::STATUS_VALIDATED,
        ];

        $qb
            ->innerJoin('cp.ask', 'a')
            ->innerJoin('a.matching', 'm')
            ->innerJoin('m.waypoints', 'wo', 'WITH', 'wo.position = 0')
            ->leftJoin('wo.address', 'ao')
            ->innerJoin('m.waypoints', 'wd', 'WITH', 'wd.position != 0 AND wd.destination = 1')
            ->leftJoin('wd.address', 'ad')
            ->where('cp.driver = :driver')
            ->andWhere('cp.type = :class')
            ->andWhere('cp.status = :status')
            ->andWhere('cp.createdDate >= :referenceDate')
            ->andWhere('ao.addressCountry = :country OR ad.addressCountry = :country')
        ;

        if (!empty($allreadyDeaclaredJourneys)) {
            $qb->andWhere($qb->expr()->notIn('cp.id', $allreadyDeaclaredJourneys));
        }

        if (!is_null($excludeId)) {
            $qb
                ->andWhere('cp.id != :excludeId')
            ;
            $parameters['excludeId'] = $excludeId;
        }

        if ($isLongDistanceProcess) {
            $qb
                ->innerJoin('a.carpoolItems', 'c', 'WITH', 'c.creditorUser = :driver')
                ->andWhere('m.commonDistance >= :distance')
                ->andWhere('c.creditorStatus = :creditorStatusOnline OR c.creditorStatus = :creditorStatusDirect')
            ;
            $parameters['creditorStatusOnline'] = CarpoolItem::DEBTOR_STATUS_ONLINE;
            $parameters['creditorStatusDirect'] = CarpoolItem::DEBTOR_STATUS_DIRECT;
        } else {
            $qb->andWhere('m.commonDistance < :distance');
        }

        $qb
            ->setParameters($parameters)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProofsToSendAsHistory(bool $longDistance = true)
    {
        $users = $this->_userRepository->findUsersCeeSubscribed();

        $qb = $this->repository->createQueryBuilder('cp');

        $parameters = [
            'distance' => CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS,
            'status' => CarpoolProof::STATUS_VALIDATED,
            'users' => $users,
        ];

        $qb
            ->innerJoin('cp.driver', 'd', 'WITH', 'd.id IN (:users)')
            ->innerJoin('cp.ask', 'a')
            ->innerJoin('a.matching', 'm')
            ->where('cp.status = :status')
            ->andWhere('cp.createdDate > s.createdAt')
        ;

        switch ($longDistance) {
            case true:
                $parameters['creditor_status'] = CarpoolItem::CREDITOR_STATUS_ONLINE;

                $qb
                    ->innerJoin('cp.mobConnectLongDistanceJourney', 'mldj')
                    ->innerJoin('mldj.subscription', 's')
                    ->innerJoin('a.carpoolItems', 'ci', 'WITH', 'ci.creditorStatus = :creditor_status')
                    ->andWhere('m.commonDistance >= :distance')
                ;

                break;

            case false:
                $parameters['type_c'] = CarpoolProof::TYPE_HIGH;

                $qb
                    ->innerJoin('cp.mobConnectShortDistanceJourney', 'msdj')
                    ->innerJoin('msdj.subscription', 's')
                    ->andWhere('cp.type = :type_c')
                    ->andWhere('m.commonDistance < :distance')
                ;

                break;
        }

        $qb
            ->andWhere('s.commitmentProofDate IS NOT NULL')
            ->setParameters($parameters)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUserCEEEligibleProof(User $user)
    {
        $allreadyAdded = [];
        $journeys = $user->getShortDistanceSubscription()->getJourneys();
        foreach ($journeys as $journey) {
            $allreadyAdded[] = $journey->getCarpoolProof()->getId();
        }

        $parameters = [
            'country' => Validation::REFERENCE_COUNTRY,
            'distance' => CeeSubscriptions::LONG_DISTANCE_MINIMUM_IN_METERS,
            'driver' => $user,
            'subscriptionDate' => $user->getShortDistanceSubscription()->getCreatedAt(),
            'class' => CarpoolProof::TYPE_HIGH,
            'status' => CarpoolProof::STATUS_VALIDATED,
            'allreadyAdded' => !empty($allreadyAdded) ? $allreadyAdded : '',
        ];

        $qb = $this->repository->createQueryBuilder('cp');

        $qb
            ->innerJoin('cp.ask', 'a')
            ->innerJoin('a.matching', 'm')
            ->innerJoin('m.waypoints', 'wo', 'WITH', 'wo.destination = 0 AND wo.position = 0')
            ->innerJoin('m.waypoints', 'wd', 'WITH', 'wd.destination = 1 AND wd.position != 0')
            ->innerJoin('wo.address', 'ao')
            ->innerJoin('wd.address', 'ad')
            ->where('cp.driver = :driver')
            ->andWhere('cp.createdDate >= :subscriptionDate')
            ->andWhere('cp.id NOT IN (:allreadyAdded)')
            ->andWhere('ao.addressCountry = :country OR ad.addressCountry = :country')
            ->andWhere('m.commonDistance < :distance')
            ->andWhere('cp.type = :class')
            ->andWhere('cp.status = :status')
        ;

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }

    public function findByAsk(Ask $ask)
    {
        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT *
            FROM carpool_proof cp
            WHERE cp.ask_id = '.$ask->getId()
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find carpools ready to end.
     *
     * @return CarpoolProof[]
     *
     * SELECT *
     * FROM carpool_proof cp
     * JOIN ask a ON cp.ask_id = a.id
     * JOIN matching m ON a.matching_id = m.id
     * JOIN criteria c ON m.criteria_id = c.id
     * JOIN `user` driver ON cp.driver_id = driver.id
     * JOIN `user` passenger ON cp.passenger_id = passenger.id
     * WHERE
     *     TIMESTAMPADD(SECOND, m.new_duration, ADDTIME(c.from_date,c.from_time)) BETWEEN "2024-01-29 09:55" AND "2024-01-29 10:10"
     *     AND (
     *         (SELECT COUNT((
     *             SELECT n.id
     *             FROM notified n
     *             WHERE n.user_id = driver.id
     *             AND n.notification_id = 172
     *             AND n.sent_date BETWEEN DATE_SUB(ADDTIME(STR_TO_DATE("2024-02-02", '%Y-%m-%d'), "08:00"), INTERVAL 10 MINUTE) AND DATE_ADD(ADDTIME(STR_TO_DATE("2024-02-02", '%Y-%m-%d'), "08:00"), INTERVAL 10 MINUTE)
     *         )) >=1)
     *         OR (SELECT COUNT((
     *             SELECT n.id
     *             FROM notified n
     *             WHERE n.user_id = driver.id
     *             AND n.notification_id = 172
     *             AND n.sent_date BETWEEN DATE_SUB(ADDTIME(STR_TO_DATE("2024-02-02", '%Y-%m-%d'), "08:00"), INTERVAL 10 MINUTE) AND DATE_ADD(ADDTIME(STR_TO_DATE("2024-02-02", '%Y-%m-%d'), "08:00"), INTERVAL 10 MINUTE)
     *         )) >= 1)
     *     );
     */
    public function findCarpoolsReadyToEnd(\DateTimeInterface $startDate, \DateTimeInterface $endDate, int $timeMargin): array
    {
        $startDate = $startDate->format('Y-m-d H:i');
        $endDate = $endDate->format('Y-m-d H:i');

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata(CarpoolProof::class, 'cp');

        $sql = "SELECT * FROM carpool_proof cp JOIN ask a ON cp.ask_id = a.id JOIN matching m ON a.matching_id = m.id JOIN criteria c ON m.criteria_id = c.id JOIN `user` driver ON cp.driver_id = driver.id JOIN `user` passenger ON cp.passenger_id = passenger.id WHERE TIMESTAMPADD(SECOND, m.new_duration, ADDTIME(c.from_date, c.from_time)) BETWEEN '{$startDate}' AND '{$endDate}' AND ( (SELECT COUNT(( SELECT n.id FROM notified n WHERE n.user_id = driver.id AND n.notification_id = 172 AND n.sent_date BETWEEN DATE_SUB(ADDTIME(c.from_date, c.from_time), INTERVAL {$timeMargin} MINUTE) AND DATE_ADD(ADDTIME(c.from_date, c.from_time), INTERVAL {$timeMargin} MINUTE) )) >=1) OR (SELECT COUNT(( SELECT n.id FROM notified n WHERE n.user_id = driver.id AND n.notification_id = 172 AND n.sent_date BETWEEN DATE_SUB(ADDTIME(c.from_date, c.from_time), INTERVAL {$timeMargin} MINUTE) AND DATE_ADD(ADDTIME(c.from_date, c.from_time), INTERVAL {$timeMargin} MINUTE) )) >= 1) );";

        return $this->entityManager->createNativeQuery($sql, $rsm)->getResult();
    }

    public function getConcurrentProofs(CarpoolProof $proof)
    {
        $usersToTest = $proof->getDriver()->getId().', '.$proof->getPassenger()->getId();
        $departudeDateTimeToTest = $proof->getStartDriverDate()->format('Y-m-d H:i');
        $arrivalDateTimeToTest = $proof->getEndDriverDate()->format('Y-m-d H:i');

        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT *
        FROM carpool_proof cp
        WHERE (cp.driver_id IN ('.$usersToTest.') OR cp.passenger_id IN ('.$usersToTest.')) AND ((cp.start_driver_date BETWEEN \''.$departudeDateTimeToTest.'\' AND \''.$arrivalDateTimeToTest.'\') OR (cp.end_driver_date BETWEEN \''.$departudeDateTimeToTest.'\' AND \''.$arrivalDateTimeToTest.'\'))'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getSplittedTripProofs(CarpoolProof $proof)
    {
        $usersToTest = $proof->getDriver()->getId().', '.$proof->getPassenger()->getId();
        $origin = $proof->getOriginDriverAddress()->getAddressLocality();
        $depertureDateTimeMinus30Min = (clone $proof->getStartDriverDate())->modify('-30 minutes')->format('Y-m-d H:i');
        $departureDateTimePlus30Min = (clone $proof->getStartDriverDate())->modify('+30 minutes')->format('Y-m-d H:i');

        $stmt = $this->entityManager->getConnection()->prepare(
            'SELECT *
        FROM carpool_proof cp
        JOIN address a ON cp.destination_driver_address_id = a.id
        WHERE (cp.driver_id IN ('.$usersToTest.') AND cp.passenger_id IN ('.$usersToTest.')) AND a.address_locality = \''.$origin.'\' AND (cp.start_driver_date BETWEEN \''.$depertureDateTimeMinus30Min.'\' AND \''.$departureDateTimePlus30Min.'\')'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
