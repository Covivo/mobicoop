<?php

namespace App\Incentive\Repository;

use App\Incentive\Entity\ShortDistanceJourney;
use App\Incentive\Service\CeeJourneyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ShortDistanceJourneyRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var EntityRepository
     */
    private $_repository;

    /**
     * @var int
     */
    private $_deadline;

    public function __construct(EntityManagerInterface $em, int $deadline)
    {
        $this->_em = $em;
        $this->_repository = $this->_em->getRepository(ShortDistanceJourney::class);

        $this->_deadline = $deadline;
    }

    /**
     * Returns shortDistanceJourneys where:
     *      - createdAt > now - env:EEC_JOURNEY_DECLARATION_DEADLINE
     *      - status.
     */
    public function getReadyForVerify(): array
    {
        $deadline = new \DateTime('now');
        $deadline->sub(new \DateInterval('P'.$this->_deadline.'D'));

        $qb = $this->_repository->createQueryBuilder('j');

        $qb
            ->where('j.createdAt <= :deadline')
            ->andWhere('j.verificationStatus = :pending')
            ->andWhere('j.rank = :first')
            ->setParameters([
                'deadline' => $deadline,
                'pending' => CeeJourneyService::VERIFICATION_STATUS_PENDING,
                'first' => CeeJourneyService::LOW_THRESHOLD_PROOF,
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
