<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\MassCommunication\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\MassCommunication\Entity\Campaign;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\MassCommunication\Service\CampaignManager;

/**
 * Data persister for Campaign
 * Use for check if we want to send the campaign to all the user
 *
 * @author Julien Deschampt <julien.deschampt@mobicoop.org>
 */

final class CampaignPutDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $campaignManager;

    public function __construct(RequestStack $requestStack, CampaignManager $campaignManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->campaignManager = $campaignManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Campaign && isset($context['item_operation_name']) &&  $context['item_operation_name'] == 'put' && $data->getSendAll() !== null;
    }

    public function persist($data, array $context = [])
    {
        // call your persistence layer to save $data
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad campaign id is provided"));
        }
        //We send the campaign to all user who accept email
        $data = $this->campaignManager->setDeliveriesCampaignToAll($data);
        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
