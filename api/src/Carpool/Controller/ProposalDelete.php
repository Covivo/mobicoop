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

use App\Carpool\Service\ProposalManager;
use App\Carpool\Entity\Proposal;
use App\DataProvider\Entity\Response;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for proposal delete.
 *
 */
class ProposalDelete
{
    use TranslatorTrait;
    private $proposalManager;
    private $request;

    public function __construct(ProposalManager $proposalManager, RequestStack $requestStack)
    {
        $this->proposalManager = $proposalManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * This method is invoked when a proposal is deleted.
     *
     * @param Proposal $data
     * @return Response
     * @throws \Exception
     */
    public function __invoke(Proposal $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad proposal id is provided"));
        }
        $data = $this->proposalManager->deleteProposal($data, json_decode($this->request->getContent(), true));
        return $data;
    }
}
