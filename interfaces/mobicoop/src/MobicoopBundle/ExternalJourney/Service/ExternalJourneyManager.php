<?php
/**
 * Created by PhpStorm.
 * User: Sofiane Belaribi
 * Date: 31/10/2018
 * Time: 10:57
 */

namespace Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service;

use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourney;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourneyProvider;

class ExternalJourneyManager
{
    private $dataProvider;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(ExternalJourney::class);
    
    }

    /**
     * Get external journeys.
     *
     * @return void
     */
    public function getExternalJourney()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get external journey providers.
     * 
     * @return void
     */
    public function getExternalJourneyProviders()
    {   
        $this->dataProvider->setClass(ExternalJourneyProvider::class);
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null; 
    }
}
