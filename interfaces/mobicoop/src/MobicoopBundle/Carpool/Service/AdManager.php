<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;

/**
 * Ad management service.
 */
class AdManager
{
    private $proposalManager;
    
    public function __construct(ProposalManager $proposalManager)
    {
        $this->proposalManager = $proposalManager;
    }
    
    /**
     * Prepare the ad before creating a proposal
     * @param Ad $ad
     * @param Request $request
     * @return \Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad
     */
    public function prepareAd(Ad $ad, Request $request)
    {
        if ($origin = $request->get('ad_form')['originAddress']) {
            if (isset($origin['longitude'])) {
                $ad->setOriginLongitude($origin['longitude']);
            }
            if (isset($origin['latitude'])) {
                $ad->setOriginLatitude($origin['latitude']);
            }
            $ad->setOrigin(
                ($origin['streetAddress'] ? $origin['streetAddress'] . ' ' : '') .
                ($origin['postalCode'] ? $origin['postalCode'] . ' '  : '') .
                ($origin['addressLocality'] ? $origin['addressLocality'] . ' '  : '') .
                ($origin['addressCountry'] ? $origin['addressCountry'] : '')
                );
        }
        if ($destination = $request->get('ad_form')['destinationAddress']) {
            if (isset($destination['longitude'])) {
                $ad->setDestinationLongitude($destination['longitude']);
            }
            if (isset($destination['latitude'])) {
                $ad->setDestinationLatitude($destination['latitude']);
            }
            $ad->setDestination(
                ($destination['streetAddress'] ? $destination['streetAddress'] . ' '  : '') .
                ($destination['postalCode'] ? $destination['postalCode'] . ' '  : '') .
                ($destination['addressLocality'] ? $destination['addressLocality'] . ' '  : '') .
                ($destination['addressCountry'] ? $destination['addressCountry'] : '')
            );
        }
        return $ad;
    }
    
    /**
     * Create an ad and the resulting proposal
     */
    public function createAd(Ad $ad)
    {
        return true;
    }
}
