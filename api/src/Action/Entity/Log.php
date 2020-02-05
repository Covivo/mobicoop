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

/**
 * User actions log.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
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
     * @Groups("read")
     */
    private $id;

    /**
     * @var \DateTimeInterface Creation date of the log action.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $date;

    /**
     * @var User The user that make the action (or the user for whom the action is made).
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="logs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Action The action.
     *
     * @ORM\ManyToOne(targetEntity="\App\Action\Entity\Action")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var User|null Admin if the action is made by an administrator for a user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="logsAdmin")
     * @Groups({"read","write"})
     */
    private $admin;

    /**
     * @var Proposal|null The proposal if the action concerns a proposal.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Proposal")
     * @Groups({"read","write"})
     */
    private $proposal;

    /**
     * @var Matching|null The matching if the action concerns a matching.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching")
     * @Groups({"read","write"})
     */
    private $matching;

    /**
     * @var Ask|null The ask if the action concerns an ask.
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask")
     * @Groups({"read","write"})
     */
    private $ask;

    /**
     * @var Article|null The article if the action concerns an article.
     *
     * @ORM\ManyToOne(targetEntity="\App\Article\Entity\Article")
     * @Groups({"read","write"})
     */
    private $article;

    /**
     * @var Event|null The event if the action concerns an event.
     *
     * @ORM\ManyToOne(targetEntity="\App\Event\Entity\Event")
     * @Groups({"read","write"})
     */
    private $event;

    /**
     * @var Community|null The community if the action concerns a community.
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community")
     * @Groups({"read","write"})
     */
    private $community;

    /**
     * @var Solidary|null The solidary record if the action concerns a solidary record.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\Solidary")
     * @Groups({"read","write"})
     */
    private $solidary;

    /**
     * @var Territory|null The territory if the action concerns a territory.
     *
     * @ORM\ManyToOne(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"read","write"})
     */
    private $territory;

    /**
     * @var Car|null The car if the action concerns a car.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\Car")
     * @Groups({"read","write"})
     */
    private $car;

    /**
     * @var User|null The user if the action concerns a user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @Groups({"read","write"})
     */
    private $userRelated;

    /**
     * @var Message|null The message if the action concerns a message.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Message")
     * @Groups({"read","write"})
     */
    private $message;

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

    public function getAdmin(): User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

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
