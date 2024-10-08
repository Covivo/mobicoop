<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\User\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Controller\CreateIdentityProofAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * An identity proof.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
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
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "post"={
 *             "controller"=CreateIdentityProofAction::class,
 *             "deserialize"=false,
 *             "security_post_denormalize"="is_granted('user_proof',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "ADMIN_post"={
 *             "path"="/admin/identity_proofs",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Users"}
 *              }
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/identity_proofs/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_user_proof',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 * @Vich\Uploadable
 */
class IdentityProof
{
    public const STATUS_NONE = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REFUSED = 3;
    public const STATUS_CANCELED = 4;

    public const INTERVALS_REMINDER = [
        '+7 day',
        '+14 day',
        '+21 day',
    ];

    /**
     * @var int the id of this identity proof
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","aRead"})
     */
    private $id;

    /**
     * @var int the status of the proof (pending/accepted/refused)
     *
     * @ORM\Column(type="integer")
     * @Groups({"aRead","aWrite","readUser"})
     */
    private $status;

    /**
     * @var User the user that linked with the proof
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", cascade={"persist"}, inversedBy="identityProofs")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @MaxDepth(1)
     * @Groups({"aWrite"})
     */
    private $user;

    /**
     * @var null|int the user id associated with the proof
     *
     * @Groups({"aWrite"})
     */
    private $userId;

    /**
     * @var User the user that validates/invalidates the proof
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $admin;

    /**
     * @var string the final file name of the proof
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","aRead"})
     */
    private $fileName;

    /**
     * @var string the original file name of the proof
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","aRead"})
     */
    private $originalName;

    /**
     * @var int the size in bytes of the file
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","aRead"})
     */
    private $size;

    /**
     * @var string the mime type of the file
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","aRead"})
     */
    private $mimeType;

    /**
     * @var File
     *
     * @Assert\NotBlank(groups={"write"})
     * @Vich\UploadableField(mapping="identityProof", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     * @Groups({"write"})
     */
    private $file;

    /**
     * @var \DateTimeInterface creation date of the proof
     *
     * @ORM\Column(type="datetime")
     * @Groups({"aRead","readUser"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the proof
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface accepted date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("aRead")
     */
    private $acceptedDate;

    /**
     * @var \DateTimeInterface refusal date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("aRead")
     */
    private $refusedDate;

    /**
     * @var bool validate the user identity
     *
     * @Groups("aWrite")
     */
    private $validate;

    /**
     * @var null|string human readable size of the file
     *
     * @Groups({"aRead"})
     */
    private $fileSize;

    /**
     * @var string validator of the user identity
     *
     * @Groups("aRead")
     */
    private $validator;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
        $this->getUser()->setIdentityStatus($status);
        if (self::STATUS_ACCEPTED == $this->status) {
            $this->setAcceptedDate(new \DateTime());
        } elseif (self::STATUS_REFUSED == $this->status) {
            $this->setRefusedDate(new \DateTime());
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName)
    {
        $this->originalName = $originalName;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType)
    {
        $this->mimeType = $mimeType;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

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

    public function getAcceptedDate(): ?\DateTimeInterface
    {
        return $this->acceptedDate;
    }

    public function setAcceptedDate(?\DateTimeInterface $acceptedDate): self
    {
        $this->acceptedDate = $acceptedDate;

        return $this;
    }

    public function getRefusedDate(): ?\DateTimeInterface
    {
        return $this->refusedDate;
    }

    public function setRefusedDate(?\DateTimeInterface $refusedDate): self
    {
        $this->refusedDate = $refusedDate;

        return $this;
    }

    public function setValidate(bool $validate): self
    {
        $this->validate = $validate;

        return $this;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(?string $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getValidator(): ?string
    {
        return $this->admin ? $this->admin->getGivenName().' '.$this->admin->getShortFamilyName() : null;
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
