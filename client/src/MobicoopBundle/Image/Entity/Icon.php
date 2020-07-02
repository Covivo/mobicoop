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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Image\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * An icon.
 */
class Icon implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this image.
     *
     */
    private $id;
    
    /**
     * @var string The iri of this image.
     *
     */
    private $iri;

    /**
     * @var string The name of the icon.
     */
    private $name;

    /**
     * @var string The filename of the icon.
     */
    private $fileName;

    /**
     * @var string The url of the icon.
     */
    private $url;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/icons/".$id);
        }
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
    
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
    
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'iri'               => $this->getIri(),
            'name'              => $this->getName(),
            'filename'          => $this->getFileName(),
            'url'               => $this->getUrl()
        ];
    }
}
