<?php

namespace App\Solidary\Service;

use App\Geography\Entity\Territory;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TerritoryOperatorManager
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var TokenStorageInterface
     */
    private $_tokenStorage;

    /**
     * @var User
     */
    private $_operator;

    /**
     * @var Territory[]
     */
    private $_territories;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $ts)
    {
        $this->_em = $em;
        $this->_tokenStorage = $ts;
    }

    /**
     * @param Territories[] $territories
     */
    public function getOperatorTerritories($territories = [])
    {
        $this->_territories = $territories;

        $this->_operator = $this->_tokenStorage->getToken()->getUser();

        if ($this->_operator->isSolidaryOperator()) {
            $structures = [];

            foreach ($this->_operator->getOperates() as $operate) {
                array_push($structures, $operate->getStructure());
            }

            foreach ($structures as $structure) {
                foreach ($structure->getTerritories() as $structureTerritory) {
                    if (!$this->_isTerritoryAllreadyExists($structureTerritory)) {
                        array_push($this->_territories, $structureTerritory);
                    }
                }
            }
        }

        return $this->_territories;
    }

    private function _isTerritoryAllreadyExists(Territory $territory): bool
    {
        return 0 < count(array_filter($this->_territories, function ($t) use ($territory) {
            $t->getId() === $territory->getId();
        }));
    }
}
