<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Action\Event;

use App\Action\Entity\Action;
use App\Article\Entity\Article;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Communication\Entity\Message;
use App\Community\Entity\Community;
use App\Event\Entity\Event as EntityEvent;
use App\Geography\Entity\Territory;
use App\MassCommunication\Entity\Campaign;
use App\Solidary\Entity\Solidary;
use App\User\Entity\Car;
use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use App\Payment\Entity\CarpoolPayment;
use App\Payment\Entity\CarpoolItem;

/**
 * Event sent when an Action is made
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActionEvent extends Event
{
    public const NAME = 'action';

    private $action;
    private $user;
    private $userDelegate;
    private $userRelated;
    private $proposal;
    private $matching;
    private $ask;
    private $article;
    private $event;
    private $community;
    private $solidary;
    private $territory;
    private $car;
    private $message;
    private $campaign;
    private $carpoolPayment;
    private $carpoolItem;

    public function __construct(Action $action, User $user)
    {
        $this->action = $action;
        $this->user = $user;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(Action $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUserDelegate(): ?User
    {
        return $this->userDelegate;
    }

    public function setUserDelegate(User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;
        return $this;
    }

    public function getUserRelated(): ?User
    {
        return $this->userRelated;
    }

    public function setUserRelated(User $userRelated): self
    {
        $this->userRelated = $userRelated;
        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(Proposal $proposal): self
    {
        $this->proposal = $proposal;
        return $this;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }

    public function setMatching(Matching $matching): self
    {
        $this->matching = $matching;
        return $this;
    }
    public function getAsk(): ?Ask
    {
        return $this->ask;
    }

    public function setAsk(Ask $ask): self
    {
        $this->ask = $ask;
        return $this;
    }
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;
        return $this;
    }
    public function getEvent(): ?EntityEvent
    {
        return $this->event;
    }

    public function setEvent(EntityEvent $event): self
    {
        $this->event = $event;
        return $this;
    }
    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(Community $community): self
    {
        $this->community = $community;
        return $this;
    }
    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }

    public function setSolidary(Solidary $solidary): self
    {
        $this->solidary = $solidary;
        return $this;
    }
    public function getTerritory(): ?Territory
    {
        return $this->territory;
    }

    public function setTerritory(Territory $territory): self
    {
        $this->territory = $territory;
        return $this;
    }
    public function getCar(): ?Car
    {
        return $this->car;
    }

    public function setCar(Car $car): self
    {
        $this->car = $car;
        return $this;
    }
    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(Message $message): self
    {
        $this->message = $message;
        return $this;
    }
    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;
        return $this;
    }

    public function getCarpoolPayment(): ?CarpoolPayment
    {
        return $this->carpoolPayment;
    }

    public function setCarpoolPayment(CarpoolPayment $carpoolPayment): self
    {
        $this->carpoolPayment = $carpoolPayment;
        return $this;
    }

    public function getCarpoolItem(): ?CarpoolItem
    {
        return $this->carpoolItem;
    }

    public function setCarpoolItem(CarpoolItem $carpoolItem): self
    {
        $this->carpoolItem = $carpoolItem;
        return $this;
    }
}
