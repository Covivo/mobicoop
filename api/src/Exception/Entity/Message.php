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


namespace App\Exception\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Exception\Repository\MessageRepository")
 * @ORM\Table(name="errorMessages")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("shortname")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", unique="true", nullable="false")
     * @var string $shortname
     */
    private $shortname;
    
    /**
     * @ORM\Column(type="string", unique="false", nullable="true")
     * @var string $text
     */
    private $text;
    
    /**
     * @ORM\Column(type="string", unique="false", columnDefinition="enum('WARNING', 'ERROR', 'INFO', 'VERBOSE')")
     * @var string $severity
     */
    private $severity;
    
    /**
     * @ORM\Column(type="integer", unique="false")
     * @var int $code
     */
    private $code;
    
    
    /**
     * @ORM\Column(type="string", unique="false", nullable="true")
     * @var string $thrownClass
     */
    private $thrownClass;
    
    /**
     * @ORM\Column(type="string", unique="false", nullable="true")
     * @var string $locale
     */
    private $locale='en_US';
    
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @return string
     */
    public function getShortname(): string
    {
        return $this->shortname;
    }
    
    /**
     * @param string $shortname
     */
    public function setShortname(string $shortname)
    {
        $this->shortname = $shortname;
    }
    
    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }
    
    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }
    
    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }
    
    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }
    
    /**
     * @param string $severity
     */
    public function setSeverity(string $severity): void
    {
        $this->severity = $severity;
    }
    
    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }
    
    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}
