<?php

namespace Mobicoop\Bundle\MobicoopBundle\Incentive\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Response;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Incentive\Entity\Incentive;

class IncentiveManager
{
    /**
     * @var DataProvider
     */
    private $_dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->_dataProvider = $dataProvider;
        $this->_dataProvider->setClass(Incentive::class);
    }

    public function getIncentives()
    {
        $this->_dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->_dataProvider->getCollection([]);

        if (!is_null($response) && $response instanceof Response && !is_null($response->getValue())) {
            return $response->getValue();
        }

        return [];
    }

    public function getIncentive(string $incentive_id)
    {
        $this->_dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->_dataProvider->simpleGet(Incentive::RESOURCE_NAME, ['incentive_id' => $incentive_id]);

        if (!is_null($response)) {
            return $response->getValue();
        }

        return $response;
    }
}
