<?php

namespace Mobicoop\Bundle\MobicoopBundle\Territory\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Territory\Entity\Territory;

class TerritoryManager
{
    private $_dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->_dataProvider = $dataProvider;
        $this->_dataProvider->setClass(Territory::class);
    }

    public function getTerritory(int $id): ?Territory
    {
        $response = $this->_dataProvider->getItem($id);

        if (200 == $response->getCode()) {
            return $response->getValue();
        }

        return null;
    }
}
