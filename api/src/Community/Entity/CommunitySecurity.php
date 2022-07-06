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

namespace App\Community\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Community\Admin\Controller\CreateCommunitySecurityAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * The securization of a community security.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Communities"}
 *              }
 *          },
 *          "ADMIN_post_securities"={
 *              "path"="/admin/community_securities",
 *              "method"="POST",
 *              "controller"=CreateCommunitySecurityAction::class,
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "deserialize"=false,
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Communities"}
 *              }
 *          },
 *      }
 * )
 * @Vich\Uploadable
 */
class CommunitySecurity
{
    /**
     * @var int the id of this community security
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("readCommunity")
     */
    private $id;

    /**
     * @var Community the community
     *
     * @ORM\ManyToOne(targetEntity="\App\Community\Entity\Community", inversedBy="communitySecurities")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $community;

    /**
     * @var null|File The document's file
     *
     * @Vich\UploadableField(mapping="communitySecurityFile", fileNameProperty="filename", originalName="originalName")
     * @Groups({"aWrite"})
     */
    private $file;

    /**
     * @var string the filename of the community security
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $filename;

    /**
     * @var string the original file name of the proof
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","aRead"})
     */
    private $originalName;

    /**
     * @var string Id of the community (used on the post)
     *
     * @Groups({"aWrite"})
     */
    private $communityId;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file)
    {
        $this->file = $file;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename)
    {
        $this->filename = $filename;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }

    public function setCommunityId(?int $communityId): self
    {
        $this->communityId = $communityId;

        return $this;
    }
}
