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

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Communication\Entity\Medium;
use App\Communication\Entity\Message;
use App\Communication\Interfaces\MessagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary contact.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidaryContact"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryContact"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_contact',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class SolidaryContact implements MessagerInterface
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int the id of this subject
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidaryContact","writeSolidaryContact"})
     */
    private $id;

    /**
     * @var SolidarySolution The solidary solution this contact is for
     * @Assert\NotBlank
     * @Groups({"readSolidaryContact","writeSolidaryContact"})
     * @MaxDepth(1)
     */
    private $solidarySolution;

    /**
     * @var string The content (usually text) message of this contact
     * @Assert\NotBlank
     * @Groups({"readSolidaryContact","writeSolidaryContact"})
     * @MaxDepth(1)
     */
    private $content;

    /**
     * @var Medium[] List of the Medium of this contact
     * @Groups({"readSolidaryContact","writeSolidaryContact"})
     */
    private $media;

    /**
     * @var SolidaryAsk If this contact is linked to an Ask, we return it after a contact
     */
    private $solidaryAsk;

    /**
     * @var Message If this contact uses the internal message medium, contains this message
     */
    private $message;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->media = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }

    public function setSolidarySolution(SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function addMedium(Medium $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
        }

        return $this;
    }

    public function removeMedium(Medium $medium): self
    {
        if ($this->media->contains($medium)) {
            $this->media->removeElement($medium);
        }

        return $this;
    }

    public function getSolidaryAsk(): ?SolidaryAsk
    {
        return $this->solidaryAsk;
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

    public function setSolidaryAsk(SolidaryAsk $solidaryAsk): self
    {
        $this->solidaryAsk = $solidaryAsk;

        return $this;
    }
}
