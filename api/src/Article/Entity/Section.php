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

namespace App\Article\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Article\Controller\SectionDown;
use App\Article\Controller\SectionUp;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * A section of an article (an article can be divided in one or many sections).
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security_post_denormalize"="is_granted('article_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('article_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('article_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "put"={
 *              "security"="is_granted('article_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "delete"={
 *              "security"="is_granted('article_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "up"={
 *              "method"="POST",
 *              "controller"=SectionUp::class,
 *              "path"="/sections/{id}/up",
 *              "security"="is_granted('article_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "down"={
 *              "method"="POST",
 *              "controller"=SectionDown::class,
 *              "path"="/sections/{id}/down",
 *              "security"="is_granted('article_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          }
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"article":"exact"})
 */
class Section
{
    public const STATUS_PENDING = 0;
    public const STATUS_PUBLISHED = 1;

    // List of the translatable items of this entity
    public const TRANSLATABLE_ITEMS = [
        'title',
        'subTitle',
    ];

    /**
     * @var int the id of this section
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","read"})
     */
    private $id;

    /**
     * @var string the title of the section
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","read","write"})
     */
    private $title;

    /**
     * @var string the subtitle of the section
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","read","write"})
     */
    private $subTitle;

    /**
     * @var int the position of the section in the article
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","read","write"})
     */
    private $position;

    /**
     * @var int the status of publication of the section
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","read","write"})
     */
    private $status;

    /**
     * @var null|Article the article related to the section
     *
     * @ORM\ManyToOne(targetEntity="\App\Article\Entity\Article", inversedBy="sections")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $article;

    /**
     * @var ArrayCollection the paragraphs of the section
     *
     * @ORM\OneToMany(targetEntity="\App\Article\Entity\Paragraph", mappedBy="section", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"aRead","read","write"})
     * @MaxDepth(1)
     */
    private $paragraphs;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->paragraphs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle): self
    {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(?int $status)
    {
        $this->status = $status;
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

    public function getParagraphs()
    {
        return $this->paragraphs->getValues();
    }

    public function addParagraph(Paragraph $paragraph): self
    {
        if (!$this->paragraphs->contains($paragraph)) {
            $this->paragraphs[] = $paragraph;
            $paragraph->setSection($this);
        }

        return $this;
    }

    public function removeParagraph(Paragraph $paragraph): self
    {
        if ($this->paragraphs->contains($paragraph)) {
            $this->paragraphs->removeElement($paragraph);
            // set the owning side to null (unless already changed)
            if ($paragraph->getSection() === $this) {
                $paragraph->setSection(null);
            }
        }

        return $this;
    }

    public function removeParagraphs(): self
    {
        $this->paragraphs->clear();

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

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
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
