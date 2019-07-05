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

namespace App\Communication\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Communication\Entity\Action;
use App\Communication\Entity\Medium;

/**
 * A message sent by the system for an action and a medium.
 *
 * @ORM\Entity()
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"title":"partial"})
 */
class Notification
{

    /**
     * @var int The id of this notification.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var string The template file of the notification.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write"})
     */
    private $template;

    /**
     * @var bool The status of the notification (active/inactive).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $active;

    /**
     * @var bool The default status of the notification (active/inactive).
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $activeDefault;

    /**
     * @var Action|null The action.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Action")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var Medium|null The medium.
     *
     * @ORM\ManyToOne(targetEntity="\App\Communication\Entity\Medium")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $medium;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
    
    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
    
    public function setActive(bool $isActive): self
    {
        $this->active = $isActive;
        
        return $this;
    }

    public function isActiveDefault(): ?bool
    {
        return $this->activeDefault;
    }
    
    public function setActiveDefault(bool $isActiveDefault): self
    {
        $this->activeDefault = $isActiveDefault;
        
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

    public function getMedium(): Medium
    {
        return $this->medium;
    }
    
    public function setMedium(?Medium $medium): self
    {
        $this->medium = $medium;
        
        return $this;
    }
}
