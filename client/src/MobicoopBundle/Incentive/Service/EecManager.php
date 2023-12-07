<?php

namespace Mobicoop\Bundle\MobicoopBundle\Incentive\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Incentive\Entity\EecInstance;
use Symfony\Component\HttpFoundation\Response;

class EecManager
{
    /**
     * @var DataProvider
     */
    private $_dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->_dataProvider = $dataProvider;
        $this->_dataProvider->setClass(EecInstance::class, EecInstance::RESOURCE_PATH);
    }

    /**
     * Returns the instance EEC service status.
     */
    public function getEecInstance(): EecInstance
    {
        $this->_dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->_dataProvider->getCollection([]);

        if (Response::HTTP_OK === $response->getCode()) {
            return $response->getValue();
        }

        return new EecInstance();
    }
}
