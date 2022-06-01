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

namespace App\Carpool\Controller;

use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AskManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Controller class for ad ask : get an ask for a given ad.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AdAskGet
{
    use TranslatorTrait;

    private $request;
    private $askManager;
    private $security;

    public function __construct(RequestStack $requestStack, AskManager $askManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->askManager = $askManager;
        $this->security = $security;
    }

    /**
     * This method is invoked when a ad ask is requested.
     *
     * @param Ad $data The ad used to create the ask
     */
    public function __invoke(Ad $data): Ad
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad Ad id is provided'));
        }

        return $this->askManager->getAskFromAd($this->request->get('id'), $this->security->getUser()->getId());
    }
}
