<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Import\Admin\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Import\Admin\Controller\ImportRelayPointAction;
use App\Import\Admin\Controller\ImportUsersAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 *
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
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post_import"={
 *              "path"="/admin/imports/users",
 *              "method"="POST",
 *              "controller"=ImportUsersAction::class,
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_user_create',object)",
 *              "deserialize"=false,
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *          "ADMIN_post_relay_point"={
 *              "path"="/admin/imports/relaypoints",
 *              "method"="POST",
 *              "controller"=ImportRelayPointAction::class,
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_relay_point_create',object)",
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
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      }
 * )
 *
 * @Vich\Uploadable
 */
class Import
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this import
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"aRead"})
     */
    private $id;

    /**
     * @var string the Entity of the import (User, RelayPoint...)
     *
     * @Groups({"read","aRead","write"})
     */
    private $entity;

    /**
     * @var null|File The document's file
     *
     * @Vich\UploadableField(mapping="massImportFile", fileNameProperty="filename", originalName="originalName")
     *
     * @Groups({"aWrite"})
     */
    private $file;

    /**
     * @var string the filename of the import
     *
     * @Groups({"read","aRead","write"})
     */
    private $filename;

    /**
     * @var string the original file name of the proof
     *
     * @Groups({"read","aRead"})
     */
    private $originalName;

    /**
     * @var array Errors of there is any
     *
     * @Groups({"read","aRead"})
     */
    private $errors;

    /**
     * @var array Useful messages
     *
     * @Groups({"read","aRead"})
     */
    private $messages;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->errors = [];
        $this->messages = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity)
    {
        $this->entity = $entity;
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

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function setErrors(?array $errors)
    {
        $this->errors = $errors;
    }

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setMessages(?array $messages)
    {
        $this->messages = $messages;
    }
}
