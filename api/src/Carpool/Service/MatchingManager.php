<?php

namespace App\Carpool\Service;

use App\Carpool\Entity\Matching;
use App\Carpool\Repository\MatchingRepository;

class MatchingManager
{
    private $_repository;

    public function __construct(MatchingRepository $repository)
    {
        $this->_repository = $repository;
    }

    public function getMatching(int $matchinId): ?Matching
    {
        return $this->_repository->find($matchinId);
    }
}
