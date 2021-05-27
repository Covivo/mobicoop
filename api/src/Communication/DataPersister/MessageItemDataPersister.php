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

namespace App\Communication\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Communication\Entity\Message;
use App\Communication\Service\InternalMessageManager;
use App\User\Exception\BlockException;
use App\User\Service\BlockManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use App\Carpool\Service\AdManager;
use App\Carpool\Service\AskManager;
use App\Carpool\Service\ProposalManager;

/**
 * Post a Message
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class MessageItemDataPersister implements ContextAwareDataPersisterInterface
{
    private $internalMessageManager;
    private $blockManager;
    private $security;
    private $adManager;
    private $askManager;
    private $proposalManager;

    public function __construct(
        InternalMessageManager $internalMessageManager,
        BlockManager $blockManager,
        Security $security,
        AdManager $adManager,
        AskManager $askManager,
        ProposalManager $proposalManager
    ) {
        $this->internalMessageManager = $internalMessageManager;
        $this->blockManager = $blockManager;
        $this->security = $security;
        $this->adManager = $adManager;
        $this->askManager = $askManager;
        $this->proposalManager = $proposalManager;
    }
  
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Message && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'post';
    }

    public function persist($data, array $context = [])
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("No message provided"));
        }

        // We check if one of the recipient has black listed the recipient or the other way around
        // TO DO : Don't block the whole send if only one User is involved in a block
        $recipients = $data->getRecipients();
        foreach ($recipients as $recipient) {
            $blocks = $this->blockManager->getInvolvedInABlock($this->security->getUser(), $recipient->getUser());
            if (is_array($blocks) && count($blocks)>0) {
                throw new BlockException(BlockException::MESSAGE_INVOLVED_IN_BLOCK);
            }
        }

        // We check if there is an Ad id. If so, we create the ask.
        if ($data->getIdProposal()!==null && $data->getIdMatching()!==null) {

            // Create an Ad from the proposal
            $ad = $this->adManager->makeAd($this->proposalManager->get($data->getIdProposal()), $data->getUser()->getId());
            $ad->setMatchingId($data->getIdMatching());
            $ad->setAdId($data->getIdAdToRespond()); // yeah... i found it strange too, check the AdId comment in Ad entity. You don't do that, you don't set up the roles correctly
            $ad = $this->askManager->createAskFromAd($ad, false);
            $data->setIdAsk($ad->getAskId());
        }
        return $this->internalMessageManager->postMessage($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
