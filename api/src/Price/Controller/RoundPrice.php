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
 */

namespace App\Price\Controller;

use App\Price\Entity\Price;
use App\Service\FormatDataManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class RoundPrice
{
    use TranslatorTrait;

    /**
     * @var FormatDataManager
     */
    private $formatDataManager;

    private $request;

    public function __construct(FormatDataManager $formatDataManager, RequestStack $requestStack)
    {
        $this->formatDataManager = $formatDataManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * This method is invoked when a new contact is posted.
     */
    public function __invoke(Price $data): Price
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad price'));
        }

        $rounded = $this->formatDataManager->roundPrice($data->getValue(), $data->getFrequency());
        $data->setValue($rounded);

        return $data;
    }
}
