<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Controller;

use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AskManager;
use App\TranslatorTrait;

/**
 * Controller class for ad ask : creation of a ask for a given ad.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AdAskPost
{
    use TranslatorTrait;

    private $askManager;

    public const TYPE_ASK = "ask";
    public const TYPE_CONTACT = "contact";

    public function __construct(AskManager $askManager)
    {
        $this->askManager = $askManager;
    }

    /**
     * This method is invoked when a new ad ask is posted.
     *
     * @param Ad $data      The ad used to create the ask
     * @param string $type  The type of ask (formal ask or contact)
     * @return Ad
     */
    public function __invoke(Ad $data, string $type): Ad
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Ad id is provided"));
        }
        $data = $this->askManager->createAskFromAd($data, $type == self::TYPE_ASK);
        return $data;
    }
}
