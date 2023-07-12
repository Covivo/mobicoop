<?php

namespace App\Incentive\Repository;

use Doctrine\ORM\EntityManagerInterface;

class SubscriptionRepository
{
    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var EntityRepository
     */
    protected $_repository;

    /**
     * @var int
     */
    protected $_deadline;

    public function __construct(EntityManagerInterface $em, int $deadline)
    {
        $this->_em = $em;
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
            ->setParameters([
                'deadline' => $deadline,
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function getDuplicatePropertiesNumber(string $property, ?string $value): int
    {
        if (is_null($value)) {
            return 0;
        }

        $qb = $this->_repository->createQueryBuilder('s');

        $qb
            ->select('COUNT(s.id)')
            ->where('s.'.$property.' = :value')
            ->setParameters([
                'value' => $value,
            ])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }
}
