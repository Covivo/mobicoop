<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\User\Repository;

use App\App\Entity\App;
use App\Community\Entity\Community;
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Entity\Structure;
use App\Solidary\Exception\SolidaryException;
use App\User\Entity\User;
use App\User\Entity\UserExport;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class UserRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    private $entityManager;

    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->repository = $entityManager->getRepository(User::class);
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function find(int $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * Find All the users.
     *
     * @return null|User
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    /**
     * Find All the users by criteria.
     *
     * @param null|mixed $limit
     * @param null|mixed $offset
     *
     * @return null|User
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): ?array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?User
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get all the users in the communities given.
     *
     * @param null|mixed $acceptEmail
     *
     * @return null|User
     */
    public function getUserInCommunity(Community $community, $acceptEmail = null)
    {
        $qb = $this->repository->createQueryBuilder('u')
            ->leftJoin('u.communityUsers', 'c')
            ->andWhere('c.community = :community')
            ->setParameter('community', $community)
        ;

        if (null != $acceptEmail) {
            $qb->andWhere('u.newsSubscription = 1');
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Users with a specific type of SolidaryUser.
     *
     * @param string $type    Type of SolidaryUser (Beneficiary or Volunteer)
     * @param array  $filters Optionnal filters
     */
    public function findUsersBySolidaryUserType(string $type = null, array $filters = null, Structure $structureAdmin = null): ?array
    {
        $this->logger->info('Start findUsersBySolidaryUserType');

        $parameters = [
            'pseudonymizedStatus' => User::STATUS_PSEUDONYMIZED,
        ];

        $query = $this->repository->createQueryBuilder('u')
            ->join('u.solidaryUser', 'su')
            ->where('u.status != pseudonymizedStatus')
        ;

        // Type
        if (SolidaryBeneficiary::TYPE == $type) {
            $query->andWhere('su.beneficiary = true');
        } elseif (SolidaryVolunteer::TYPE == $type) {
            $query->andWhere('su.volunteer = true');
        } else {
            throw new SolidaryException(SolidaryException::TYPE_SOLIDARY_USER_UNKNOWN);
        }

        // filter by structure
        if (!is_null($structureAdmin)) {
            $query
                ->join('su.solidaryUserStructures', 'sus')
                ->andWhere('sus.structure = :structure')
            ;

            $parameters['structure'] = $structureAdmin;
        }

        // Filters
        if (!is_null($filters)) {
            foreach ($filters as $filter => $value) {
                $query->andWhere('u.'.$filter." like '%".$value."%'");
            }
        }

        $query->setParameters($parameters);

        // var_dump($structureAdmin->getId());die;
        return $query->getQuery()->getResult();
    }

    /**
     * Get users by their id if they accept emailing.
     *
     * @param array $ids The ids of the users
     *
     * @return null|array The users
     */
    public function findDeliveriesByIds(array $ids)
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.id IN(:ids) and u.newsSubscription=1')
            ->setParameter('ids', $ids)
            ->getQuery()->getResult()
        ;
    }

    /**
     * Count the active users (with a connection in the last 6 months).
     */
    public function countActiveUsers(): ?int
    {
        $now = new \DateTime();
        $last6Months = $now->modify('-6 months');

        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.lastActivityDate >= :last6months')
            ->setParameter('last6months', $last6Months)
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Count users.
     */
    public function countUsers(): ?int
    {
        $query = $this->repository->createQueryBuilder('u')
            ->select('count(u.id)')
        ;

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findUserWithNoAdSinceXDays(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('- '.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT u.id
            FROM user u
            LEFT JOIN proposal p on p.user_id = u.id and p.private=0
            WHERE DATE(u.created_date) = '".$createdDate."'
            GROUP BY u.id
            HAVING COUNT(p.id) = 0"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findNewlyRegisteredUsers(): ?array
    {
        $now = (new \DateTime('now'));
        $yesterday = $now->modify('-1 day')->format('Y-m-d');

        $query = $this->repository->createQueryBuilder('u')
            ->select('u')
            ->where('u.createdDate = :yesterday')
            ->setParameter('yesterday', $yesterday)
        ;

        return $query->getQuery()->getResult();
    }

    public function findUserWithOlderThanXDaysAd(int $nbOfDays = null): ?array
    {
        $now = (new \DateTime('now'));
        $createdDate = $now->modify('-'.$nbOfDays.' days')->format('Y-m-d');

        $stmt = $this->entityManager->getConnection()->prepare(
            "SELECT ponct.id
            FROM
                (SELECT id
                    FROM
                        (SELECT u.id , max(p.created_date) AS maxdate
                        FROM user u
                            INNER JOIN proposal p ON p.user_id = u.id
                            INNER JOIN criteria c ON c.id = p.criteria_id
                        WHERE p.private=0 AND c.frequency=1
                        GROUP BY u.id) AS maxpropdate
                    WHERE DATE(maxdate) = '".$createdDate."') AS ponct
                LEFT JOIN
                    (SELECT u.id
                    FROM user u
                        INNER JOIN proposal p ON p.user_id = u.id
                        INNER JOIN criteria c ON c.id = p.criteria_id
                    WHERE p.private = 0 AND c.frequency= 2 AND c.to_date >= NOW()
                    GROUP BY u.id) AS regul ON regul.id = ponct.id
            WHERE regul.id IS NULL"
        );

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByLastActivityDate(\DateTime $lastActivityDate): ?array
    {
        $dateCondition = '
            (u.lastActivityDate is not null and u.lastActivityDate >= :lastActivityDateBottom and u.lastActivityDate <= :lastActivityDateUp)
            OR
            (u.lastActivityDate is null and u.createdDate >= :lastActivityDateBottom and u.createdDate <= :lastActivityDateUp)
        ';

        $query = $this->repository->createQueryBuilder('u')
            ->where($dateCondition)
            ->andwhere('u.status <> :statusPseudonymized')
            ->setParameter('lastActivityDateBottom', $lastActivityDate->format('Y-m-d').' 00:00:00')
            ->setParameter('lastActivityDateUp', $lastActivityDate->format('Y-m-d').' 23:59:59')
            ->setParameter('statusPseudonymized', User::STATUS_PSEUDONYMIZED)
        ;

        return $query->getQuery()->getResult();
    }

    public function findBeforeLastActivityDate(\DateTime $lastActivityDate): ?array
    {
        $dateCondition = '
            (u.lastActivityDate is not null and u.lastActivityDate <= :lastActivityDateUp)
            OR
            (u.lastActivityDate is null and u.createdDate <= :lastActivityDateUp)
        ';

        $query = $this->repository->createQueryBuilder('u')
            ->andwhere($dateCondition)
            ->andwhere('u.status <> :statusPseudonymized')
            ->setParameter('lastActivityDateUp', $lastActivityDate->format('Y-m-d').' 23:59:59')
            ->setParameter('statusPseudonymized', User::STATUS_PSEUDONYMIZED)
        ;

        return $query->getQuery()->getResult();
    }

    public function findForExport(array $filters, array $restrictionTerritoryIds)
    {
        $query = "SELECT
            u.family_name AS familyName,
            u.given_name AS givenName,
            CASE u.gender
                WHEN 3 THEN 'Autre'
                WHEN 1 THEN 'Femme'
                WHEN 2 THEN 'Homme'
            END AS gender,
            u.email AS email,
            u.telephone AS telephone,
            u.birth_date AS birthDate,
            u.created_date AS registrationDate,
            u.last_activity_date AS lastActivityDate,
            u.news_subscription AS newsletterSubscription,
            CASE
                WHEN u.hitch_hike_driver = 1 AND u.hitch_hike_passenger = 1 THEN '".UserExport::HITCHHIKING_BOTH."'
                WHEN u.hitch_hike_driver = 1 AND u.hitch_hike_passenger = 0 THEN '".UserExport::HITCHHIKING_DRIVER."'
                WHEN u.hitch_hike_driver = 0 AND u.hitch_hike_passenger = 1 THEN '".UserExport::HITCHHIKING_PASSENGER."'
                WHEN u.hitch_hike_driver = 0 AND u.hitch_hike_passenger = 0 THEN '".UserExport::HITCHHIKING_NONE."'
            END AS rezopouceUse,
            u.hitch_hike_driver AS hitchHikeDriver,
            u.hitch_hike_passenger AS hitchHikePassenger,
            CASE
                WHEN ip.status IS NULL THEN '".UserExport::IDENTITY_NONE."'
                WHEN ip.status = 1 THEN '".UserExport::IDENTITY_UNDER_REVIEW."'
                WHEN ip.status = 2 THEN '".UserExport::IDENTITY_VERIFIED."'
                WHEN ip.status = 3 THEN '".UserExport::IDENTITY_REJECTED."'
                WHEN ip.status = 4 THEN '".UserExport::IDENTITY_CANCELED."'
            END AS identityStatus,
            tmva_extended.MaxValiditeAnnonce AS maxValidityAnnonceDate,
            ha.address_locality AS addressLocality,
            CASE
                WHEN su.beneficiary=1 AND (su.volunteer!=1 OR su.volunteer IS NULL) THEN 'Passager solidaire'
                WHEN (su.beneficiary!=1 OR su.beneficiary IS NULL) AND su.volunteer=1 THEN 'Transporteur bénévole'
                WHEN su.beneficiary=1 AND su.volunteer=1 THEN 'Passager solidaire ET Transporteur bénévole'
                ELSE 'non'
            END AS solidaryUser,
            ftp.Annonce1_Origine AS carpool1OriginLocality,
            ftp.Annonce1_Destination AS carpool1DestinationLocality,
            ftp.Annonce1_Frequence AS carpool1Frequency,
            ftp.Annonce2_Origine AS carpool2OriginLocality,
            ftp.Annonce2_Destination AS carpool2DestinationLocality,
            ftp.Annonce2_Frequence AS carpool2Frequency,
            ftp.Annonce3_Origine AS carpool3OriginLocality,
            ftp.Annonce3_Destination AS carpool3DestinationLocality,
            ftp.Annonce3_Frequence AS carpool3Frequency,
            ftp.NombreAnnonces AS adNumber,
            ftcu.Communauté1 AS community1,
            ftcu.Communauté2 AS community2,
            ftcu.Communauté3 AS community3,
            ftcu.Communauté4 AS community4,
            ftcu.Communauté5 AS community5,
            ftcu.Communauté6 AS community6,
            ftcu.Communauté7 AS community7,
            ftcu.Communauté8 AS community8,
            ftcu.Communauté9 AS community9,
            ftcu.Communauté10 AS community10,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 1 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSuperAdmin,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 2 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleAdmin,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 3 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleUserRegisteredFull,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 4 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleUserRegisteredMinimal,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 5 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleUser,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 6 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleMassMatch,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 7 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleCommunityManager,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 8 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleCommunityManagerPublic,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 9 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleCommunityManagerPrivate,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 10 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryOperator,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 11 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryVolunteer,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 12 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryBeneficiary,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 16 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleCommunicationManager,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 171 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryVolunteerCandidate,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 172 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryBeneficiaryCandidate,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 257 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleInteroperability,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 274 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleSolidaryAdmin,
            IF ((SELECT uaa.id FROM user_auth_assignment uaa WHERE uaa.user_id = u.id AND uaa.auth_item_id = 306 GROUP BY uaa.auth_item_id) IS NOT NULL, 'Oui', 'Non') AS roleTerritoryConsultant
        FROM user u
            LEFT OUTER JOIN address ha ON ha.user_id = u.id AND ha.home=1
            LEFT OUTER JOIN solidary_user su ON u.solidary_user_id = su.id
            LEFT OUTER JOIN identity_proof ip ON ip.user_id = u.id ";

        if (!empty($restrictionTerritoryIds)) {
            $query .= '
            INNER JOIN address_territory at2 ON ha.id = at2.address_id AND at2.territory_id IN ('.join(',', $restrictionTerritoryIds).')
            ';
        }

        foreach ($filters as $filter => $value) {
            switch ($filter) {
                case 'community':
                    $query .= "INNER JOIN community_user cu ON u.id = cu.user_id AND cu.community_id = {$value}
                    ";

                    break;
            }
        }

        $query .= "LEFT OUTER JOIN (
                    SELECT
                        tcu_extended.user_id,
                        GROUP_CONCAT(tcu_extended.Communauté1) AS Communauté1,
                        GROUP_CONCAT(tcu_extended.Communauté2) AS Communauté2,
                        GROUP_CONCAT(tcu_extended.Communauté3) AS Communauté3,
                        GROUP_CONCAT(tcu_extended.Communauté4) AS Communauté4,
                        GROUP_CONCAT(tcu_extended.Communauté5) AS Communauté5,
                        GROUP_CONCAT(tcu_extended.Communauté6) AS Communauté6,
                        GROUP_CONCAT(tcu_extended.Communauté7) AS Communauté7,
                        GROUP_CONCAT(tcu_extended.Communauté8) AS Communauté8,
                        GROUP_CONCAT(tcu_extended.Communauté9) AS Communauté9,
                        GROUP_CONCAT(tcu_extended.Communauté10) AS Communauté10
                    FROM
                        (SELECT
                        tcu.user_id,
                        CASE WHEN tcu.OrdreCommunaute = 1 THEN tcu.NomCommunaute END AS Communauté1,
                        CASE WHEN tcu.OrdreCommunaute = 2 THEN tcu.NomCommunaute END AS Communauté2,
                        CASE WHEN tcu.OrdreCommunaute = 3 THEN tcu.NomCommunaute END AS Communauté3,
                        CASE WHEN tcu.OrdreCommunaute = 4 THEN tcu.NomCommunaute END AS Communauté4,
                        CASE WHEN tcu.OrdreCommunaute = 5 THEN tcu.NomCommunaute END AS Communauté5,
                        CASE WHEN tcu.OrdreCommunaute = 6 THEN tcu.NomCommunaute END AS Communauté6,
                        CASE WHEN tcu.OrdreCommunaute = 7 THEN tcu.NomCommunaute END AS Communauté7,
                        CASE WHEN tcu.OrdreCommunaute = 8 THEN tcu.NomCommunaute END AS Communauté8,
                        CASE WHEN tcu.OrdreCommunaute = 9 THEN tcu.NomCommunaute END AS Communauté9,
                        CASE WHEN tcu.OrdreCommunaute = 10 THEN tcu.NomCommunaute END AS Communauté10
                        FROM
                        (SELECT cu.user_id, ROW_NUMBER() OVER (PARTITION BY cu.user_id ORDER BY cu.accepted_date ASC) AS OrdreCommunaute, c.name AS NomCommunaute, cu.accepted_date AS DateAcceptationCommunaute
                        FROM community_user cu
                            INNER JOIN community c ON c.id = cu.community_id
                        WHERE cu.accepted_date IS NOT NULL
                        GROUP by cu.id ORDER BY cu.accepted_date) AS tcu
                        ) AS tcu_extended
                    GROUP BY tcu_extended.user_id
                    )
                    AS ftcu ON u.id = ftcu.user_id
            LEFT OUTER JOIN (
                    SELECT
                        tp_extended.user_id,
                        GROUP_CONCAT(tp_extended.Annonce1_Origine) AS Annonce1_Origine,
                        GROUP_CONCAT(tp_extended.Annonce1_Destination) AS Annonce1_Destination,
                        GROUP_CONCAT(tp_extended.Annonce1_Frequence) AS Annonce1_Frequence,
                        GROUP_CONCAT(tp_extended.Annonce2_Origine) AS Annonce2_Origine,
                        GROUP_CONCAT(tp_extended.Annonce2_Destination) AS Annonce2_Destination,
                        GROUP_CONCAT(tp_extended.Annonce2_Frequence) AS Annonce2_Frequence,
                        GROUP_CONCAT(tp_extended.Annonce3_Origine) AS Annonce3_Origine,
                        GROUP_CONCAT(tp_extended.Annonce3_Destination) AS Annonce3_Destination,
                        GROUP_CONCAT(tp_extended.Annonce3_Frequence) AS Annonce3_Frequence,
                        MAX(tp_extended.NombreAnnonces) AS NombreAnnonces
                    FROM
                        (SELECT
                            tp.user_id,
                            CASE WHEN tp.OrdreAnnonce=1 THEN tp.AnnonceOrigine END AS Annonce1_Origine,
                            CASE WHEN tp.OrdreAnnonce=1 THEN tp.AnnonceDestination END AS Annonce1_Destination,
                            CASE WHEN tp.OrdreAnnonce=1 THEN tp.AnnonceFrequence END AS Annonce1_Frequence,
                            CASE WHEN tp.OrdreAnnonce=2 THEN tp.AnnonceOrigine END AS Annonce2_Origine,
                            CASE WHEN tp.OrdreAnnonce=2 THEN tp.AnnonceDestination END AS Annonce2_Destination,
                            CASE WHEN tp.OrdreAnnonce=2 THEN tp.AnnonceFrequence END AS Annonce2_Frequence,
                            CASE WHEN tp.OrdreAnnonce=3 THEN tp.AnnonceOrigine END AS Annonce3_Origine,
                            CASE WHEN tp.OrdreAnnonce=3 THEN tp.AnnonceDestination END AS Annonce3_Destination,
                            CASE WHEN tp.OrdreAnnonce=3 THEN tp.AnnonceFrequence END AS Annonce3_Frequence,
                            max(tp.OrdreAnnonce) AS NombreAnnonces
                        FROM
                            (SELECT
                                p.user_id,
                                ROW_NUMBER() OVER (PARTITION BY p.user_id ORDER BY p.created_date ASC) AS OrdreAnnonce,
                                ad.address_locality AS AnnonceOrigine,
                                aa.address_locality AS AnnonceDestination,
                                CASE c.frequency
                                WHEN 1 THEN 'Occasionnel'
                                WHEN 2 THEN 'Régulier'
                                END AS AnnonceFrequence
                            FROM proposal p
                                INNER JOIN criteria c ON c.id = p.criteria_id
                                INNER JOIN waypoint wd ON (wd.proposal_id = p.id AND wd.position=0)
                                INNER JOIN waypoint wa ON (wa.proposal_id = p.id AND wa.destination=1)
                                INNER JOIN address ad ON ad.id = wd.address_id
                                INNER JOIN address aa ON aa.id = wa.address_id
                            where p.private=0 AND (p.dynamic!=1 OR p.dynamic IS NULL) AND ((c.frequency=1 AND c.FROM_date > NOW()) OR c.frequency=2 AND c.to_date > NOW())
                            ) AS tp
                        GROUP BY tp.user_id, Annonce1_Origine, Annonce1_Destination, Annonce1_Frequence, Annonce2_Origine, Annonce2_Destination, Annonce2_Frequence, Annonce3_Origine, Annonce3_Destination, Annonce3_Frequence
                        ) AS tp_extended
                    GROUP BY tp_extended.user_id
                    )
                    AS ftp ON u.id = ftp.user_id
            LEFT OUTER JOIN (SELECT
                    tmva.user_id,
                    MAX(tmva.AnnonceFinValidite) AS MaxValiditeAnnonce
                FROM    (SELECT
                        p.user_id,
                        CASE c.frequency
                        WHEN 1 THEN c.FROM_date
                        WHEN 2 THEN c.to_date
                        END AS AnnonceFinValidite
                    FROM proposal p
                        INNER JOIN criteria c ON c.id = p.criteria_id
                    where p.private=0 AND (p.dynamic!=1 OR p.dynamic IS NULL)
                    ) AS tmva
                GROUP BY tmva.user_id
                ) AS tmva_extended ON u.id = tmva_extended.user_id
            WHERE u.status != ".User::STATUS_PSEUDONYMIZED.' ';

        foreach ($filters as $filter => $value) {
            switch ($filter) {
                case 'isHitchHiker':
                    $query .= 'AND u.hitch_hike_driver IS NOT NULL OR u.hitch_hike_passenger IS NOT NULL ';

                    break;
            }
        }

        $stmt = $this->entityManager->getConnection()->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findUsersCeeSubscribed()
    {
        $qb = $this->repository->createQueryBuilder('u');

        $qb
            ->leftJoin('u.longDistanceSubscription', 'lds')
            ->leftJoin('u.shortDistanceSubscription', 'sds')
            ->where('lds.id IS NOT NULL OR sds.id IS NOT NULL')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findFilteredUsers(array $filters)
    {
        $qb = $this->repository->createQueryBuilder('u');

        $parameters = [];

        foreach ($filters as $key => $value) {
            if (property_exists(User::class, $key)) {
                $qb
                    ->andWhere('u.'.$key.' like :'.$key.'Value')
                ;

                $parameters[$key.'Value'] = '%'.$value.'%';
            }
        }

        $qb->setParameters($parameters);

        return $qb->getQuery()->getResult();
    }

    public function findUserBySsoIdAndProvider(string $ssoId, string $ssoProvider): ?User
    {
        $query = $this->repository->createQueryBuilder('u');

        $query
            ->join('u.ssoAccounts', 'ssoaccounts')
            ->where('ssoaccounts.ssoId = :ssoId')
            ->andWhere('ssoaccounts.ssoProvider = :ssoProvider')
            ->setParameter('ssoId', $ssoId)
            ->setParameter('ssoProvider', $ssoProvider)
            ->orderBy('ssoaccounts.id', 'DESC')
            ->setMaxResults(1)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findUserBySsoIdAndAppDelegate(string $ssoId, App $appDelegate): ?User
    {
        $query = $this->repository->createQueryBuilder('u');

        $query
            ->join('u.ssoAccounts', 'ssoaccounts')
            ->where('ssoaccounts.ssoId = :ssoId')
            ->andWhere('ssoaccounts.appDelegate = :appDelegate')
            ->setParameter('ssoId', $ssoId)
            ->setParameter('appDelegate', $appDelegate)
            ->orderBy('ssoaccounts.id', 'DESC')
            ->setMaxResults(1)
        ;

        return $query->getQuery()->getOneOrNullResult();
    }
}
