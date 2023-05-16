<?php

namespace App\Incentive\Repository;

use App\Incentive\Entity\LongDistanceSubscription;
use Doctrine\ORM\EntityManagerInterface;

class LongDistanceSubscriptionRepository extends SubscriptionRepository
{
    public function __construct(EntityManagerInterface $em, int $deadline)
    {
        parent::__construct($em, $deadline);

        $this->_repository = $this->_em->getRepository(LongDistanceSubscription::class);
    }
}
