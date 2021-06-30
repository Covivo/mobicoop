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

namespace App\Action\Entity;

use Doctrine\ORM\Mapping as ORM;
// use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
// use ApiPlatform\Core\Annotation\ApiFilter;
// use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\User\Entity\User;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Ask;
use App\Article\Entity\Article;
use App\Event\Entity\Event;
use App\Community\Entity\Community;
use App\Solidary\Entity\Solidary;
use App\Geography\Entity\Territory;
use App\User\Entity\Car;
use App\Communication\Entity\Message;
use App\MassCommunication\Entity\Campaign;

/**
 * User actions log.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"readLog"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeLog"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('logs_read',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('log_read',object)"
 *          },
 *      }
 * )
 * ApiFilter(OrderFilter::class, properties={"id", "date"}, arguments={"orderParameterName"="order"})
 */
class Log
{
    
    /**
     * @var int The id of this log action.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     * @Groups("readLog")
     */
    private $id;

    /**
     * @var \DateTimeInterface Creation date of the log action.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     * @Groups("readLog")
     */
    private $date;

    /**
     * @var User The user that make the action (or the user for whom the action is made).
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="logs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readLog","writeLog"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Action The action.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Action\Entity\Action")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readLog","writeLog"})
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var User|null The user that makes the action for another user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="logsAsDelegate")
     * @Groups({"readLog","writeLog"})
     */
    private $userDelegate;

    /**
     * @var Proposal|null The proposal if the action concerns a proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @Groups({"readLog","writeLog"})
     */
    private $proposal;

    /**
     * @var Matching|null The matching if the action concerns a matching.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching")
     * @Groups({"readLog","writeLog"})
     */
    private $matching;

    /**
     * @var Ask|null The ask if the action concerns an ask.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask")
     * @Groups({"readLog","writeLog"})
     */
    private $ask;

    /**
     * @var Article|null The article if the action concerns an article.
     *
     * @ORM\ManyToOne(targetEntity="\App\Article\Entity\Article")
     * @Groups({"readLog","writeLog"})
     */
    private $article;

    /**
     * @var Event|null The event if the action concerns an event.
     *
     * @ORM\ManyToOne(targetEntity="\App\Event\Entity\Event")
     * @Groups({"readLog","writeLog"})
     */
    private $event;

    /**
     * @var Community|null The community if the action concerns a community.
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community")
     * @Groups({"readLog","writeLog"})
     */
    private $community;

    /**
     * @var Solidary|null The solidary record if the action concerns a solidary record.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\Solidary")
     * @Groups({"readLog","writeLog"})
     */
    private $solidary;

    /**
     * @var Territory|null The territory if the action concerns a territory.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"readLog","writeLog"})
     */
    private $territory;

    /**
     * @var Car|null The car if the action concerns a car.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\Car")
     * @Groups({"readLog","writeLog"})
     */
    private $car;

    /**
     * @var User|null The user if the action concerns a user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @Groups({"readLog","writeLog"})
     */
    private $userRelated;

    /**
     * @var Message|null The message if the action concerns a message.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Message")
     * @Groups({"readLog","writeLog"})
     */
    private $message;

    /**
     * @var Campaign|null The campaign if the action concerns a campaign.
     *
     * @ORM\ManyToOne(targetEntity="\App\MassCommunication\Entity\Campaign")
     * @Groups({"readLog","writeLog"})
     */
    private $campaign;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
    
    public function setAction(?Action $action): self
    {
        $this->action = $action;
        
        return $this;
    }

    public function getUserDelegate(): User
    {
        return $this->userDelegate;
    }

    public function setUserDelegate(?User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }
    
    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;
        
        return $this;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }
    
    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;
        
        return $this;
    }

    public function getAsk(): ?Ask
    {
        return $this->ask;
    }
    
    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }
    
    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        
        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }
    
    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        
        return $this;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }
    
    public function setCommunity(?Community $community): self
    {
        $this->community = $community;
        
        return $this;
    }

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }
    
    public function setSolidary(?Solidary $solidary): self
    {
        $this->solidary = $solidary;
        
        return $this;
    }

    public function getTerritory(): ?Territory
    {
        return $this->territory;
    }
    
    public function setTerritory(?Territory $territory): self
    {
        $this->territory = $territory;
        
        return $this;
    }

    public function getCar(): ?Car
    {
        return $this->car;
    }
    
    public function setCar(?Car $car): self
    {
        $this->car = $car;
        
        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }
    
    public function setMessage(?Message $message): self
    {
        $this->message = $message;
        
        return $this;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }
    
    public function setCampaign(?Campaign $campaign): self
    {
        $this->campaign = $campaign;
        
        return $this;
    }

    public function getUserRelated(): User
    {
        return $this->userRelated;
    }

    public function setUserRelated(?User $userRelated): self
    {
        $this->userRelated = $userRelated;

        return $this;
    }
    
    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setDate(new \Datetime());
    }
}
