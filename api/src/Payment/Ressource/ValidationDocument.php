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
 */

namespace App\Payment\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Payment\Controller\UploadValidationDocumentAction;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * A Validation Document.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/validation_documents",
 *              "controller"=UploadValidationDocumentAction::class,
 *              "deserialize"=false,
 *              "defaults"={"_api_receive"=false},
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 * @Vich\Uploadable
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ValidationDocument
{
    public const DEFAULT_ID = '999999999999';
    public const OUT_OF_DATE = 1;
    public const UNDERAGE_PERSON = 2;
    public const DOCUMENT_FALSIFIED = 3;
    public const DOCUMENT_MISSING = 4;
    public const DOCUMENT_HAS_EXPIRED = 5;
    public const DOCUMENT_NOT_ACCEPTED = 6;
    public const DOCUMENT_DO_NOT_MATCH_USER_DATA = 7;
    public const DOCUMENT_UNREADABLE = 8;
    public const DOCUMENT_INCOMPLETE = 9;
    public const SPECIFIC_CASE = 10;

    /**
     * @var int The id of this document
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var User The document's owner
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $user;

    /**
     * @var null|File The document's file
     *
     * @Vich\UploadableField(mapping="validationDocument", fileNameProperty="fileName", originalName="originalName", size="size", mimeType="mimeType")
     */
    private $file;

    /**
     * @var string The document's filename
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $fileName;

    /**
     * @var string The document's file extension
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $extension;

    /**
     * @var string the original file name of the import
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $originalName;

    /**
     * @var int the document's size in bytes
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $size;

    /**
     * @var string the document's mime type
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $mimeType;

    /**
     * @var string Payment provider's identifier of this document
     * @Groups({"readPayment","writePayment"})
     */
    private $identifier;

    /**
     * @var int The status of the validation document
     * @Groups({"readPayment","writePayment"})
     */
    private $status;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file)
    {
        $this->file = $file;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension)
    {
        $this->extension = $extension;
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

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
