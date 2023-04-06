<?php

namespace App\Incentive\Repository;

use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ShortDistanceSubscriptionRepository
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
        $this->_repository = $this->_em->getRepository(ShortDistanceSubscription::class);

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

        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->where('s.status IS NULL')
            ->andWhere('s.commitmentProofDate IS NOT NULL')
            ->andWhere('s.commitmentProofDate <= :deadline')
            ->andWhere('s.verificationDate IS NULL')
            ->setParameters([
                'deadline' => $deadline,
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}
