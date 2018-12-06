<?php
/**
 * Created by PhpStorm.
 * User: Sofiane Belaribi
 * Date: 31/10/2018
 * Time: 10:57
 */

namespace Mobicoop\Bundle\MobicoopBundle\Service;

use Mobicoop\Bundle\MobicoopBundle\Entity\ExternalJourney;

class ExternalJourneyManager
{
    private $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(ExternalJourney::class);
    }

    public function getExternalJourney()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
