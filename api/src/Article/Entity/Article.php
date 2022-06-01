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

namespace App\Article\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Action\Entity\Log;

/**
 * An article : informations that should be displayed in a page of a site or in a screen of a mobile app.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      shortName="Page",
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/pages",
 *              "security"="is_granted('article_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "externalArticles"={
 *              "method"="GET",
 *              "path"="/pages/external",
 *              "security"="is_granted('article_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/pages",
 *              "security_post_denormalize"="is_granted('article_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/articles",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_article_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/articles",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_article_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/pages/{id}",
 *              "security"="is_granted('article_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "getArticle"={
 *              "method"="GET",
 *              "path"="/articles/{id}",
 *              "security"="is_granted('article_read',object)",
  *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
*          },
 *          "put"={
 *              "method"="PUT",
 *              "path"="/pages/{id}",
 *              "security"="is_granted('article_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "delete"={
 *              "method"="DELETE",
 *              "path"="/pages/{id}",
 *              "security"="is_granted('article_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Articles"}
 *              }
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/articles/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_article_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/articles/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_article_update',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/articles/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_article_delete',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 *
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "title", "status"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"status":"exact","title":"partial"})
 */
class Article
{
    public const STATUS_PENDING = 0;
    public const STATUS_PUBLISHED = 1;
    public const NB_EXTERNAL_ARTICLES_DEFAULT = 3;

    // List of the translatable items of this entity
    public const TRANSLATABLE_ITEMS = [
        "title"
    ];

    /**
     * @var int The id of this article.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","read"})
     */
    private $id;

    /**
     * @var string The title of the article.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","read","write"})
     */
    private $title;

    /**
     * @var int The status of publication of the article.
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","read","write"})
     */
    private $status;

    /**
     * @var ArrayCollection The sections of the article.
     *
     * @ORM\OneToMany(targetEntity="\App\Article\Entity\Section", mappedBy="article", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"aRead","read","write"})
     * @MaxDepth(1)
     */
    private $sections;

    /**
     * @var string The code of the article iFrame if it's displayed from an external source
     *
     * @ORM\Column(type="string", length=512, nullable=true)
     * @Groups({"read","write"})
     */
    private $iFrame;

    /**
     * @var ArrayCollection The logs linked with the Article.
     *
     * @ORM\OneToMany(targetEntity="\App\Action\Entity\Log", mappedBy="article", cascade={"remove"})
     */
    private $logs;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    /**
     * @var array|null The sections in administration write context
     * @Groups({"aWrite"})
     */
    private $asections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getSections()
    {
        return $this->sections->getValues();
    }

    public function addSection(Section $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setArticle($this);
        }

        return $this;
    }

    public function removeSection(Section $section): self
    {
        if ($this->sections->contains($section)) {
            $this->sections->removeElement($section);
            // set the owning side to null (unless already changed)
            if ($section->getArticle() === $this) {
                $section->setArticle(null);
            }
        }

        return $this;
    }

    public function removeSections(): self
    {
        $this->sections->clear();
        return $this;
    }

    public function getIFrame(): ?string
    {
        return $this->iFrame;
    }

    public function setIFrame(?string $iFrame): self
    {
        $this->iFrame = $iFrame;

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

    public function getAsections(): ?array
    {
        return $this->asections;
    }

    public function setAsections(?array $asections)
    {
        $this->asections = $asections;

        return $this;
    }

    public function getLogs()
    {
        return $this->logs->getValues();
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setArticle($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->contains($log)) {
            $this->logs->removeElement($log);
            // set the owning side to null (unless already changed)
            if ($log->getArticle() === $this) {
                $log->setArticle(null);
            }
        }

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
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
}
