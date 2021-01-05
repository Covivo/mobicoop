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
namespace App\Journey\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A popular journey
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
 class PopularJourney
 {
     /**
      * @var string The origin of the journey
      * @Groups({"readJourney"})
      */
     private $origin;

     /**
      * @var float|null The latitude of the origin.
      * @Groups({"readJourney"})
      */
     private $latitudeOrigin;

     /**
      * @var float|null The longitude of the origin.
      * @Groups({"readJourney"})
      */
     private $longitudeOrigin;

     /**
      * @var string The destination of the journey
      * @Groups({"readJourney"})
      */
     private $destination;

     /**
      * @var float|null The latitude of the destination.
      * @Groups({"readJourney"})
      */
     private $latitudeDestination;

     /**
      * @var float|null The longitude of the destination.
      * @Groups({"readJourney"})
      */
     private $longitudeDestination;

     /**
      * @var int|null The number of occurences of this journey
      * @Groups({"readJourney"})
      */
     private $occurences;

     public function getOrigin(): ?string
     {
         return $this->origin;
     }
    
     public function setOrigin(?string $origin): self
     {
         $this->origin = $origin;

         return $this;
     }

     public function getLatitudeOrigin(): ?float
     {
         return $this->latitudeOrigin;
     }

     public function setLatitudeOrigin(float $latitudeOrigin): self
     {
         $this->latitudeOrigin = $latitudeOrigin;

         return $this;
     }

     public function getLongitudeOrigin(): ?float
     {
         return $this->longitudeOrigin;
     }

     public function setLongitudeOrigin(float $longitudeOrigin): self
     {
         $this->longitudeOrigin = $longitudeOrigin;

         return $this;
     }

     public function getDestination(): ?string
     {
         return $this->destination;
     }
    
     public function setDestination(string $destination): self
     {
         $this->destination = $destination;

         return $this;
     }

     public function getLatitudeDestination(): ?float
     {
         return $this->latitudeDestination;
     }

     public function setLatitudeDestination(float $latitudeDestination): self
     {
         $this->latitudeDestination = $latitudeDestination;

         return $this;
     }

     public function getLongitudeDestination(): ?float
     {
         return $this->longitudeDestination;
     }

     public function setLongitudeDestination(float $longitudeDestination): self
     {
         $this->longitudeDestination = $longitudeDestination;

         return $this;
     }

     public function getOccurences(): ?int
     {
         return $this->occurences;
     }
    
     public function setOccurences(?int $occurences): self
     {
         $this->occurences = $occurences;

         return $this;
     }
 }
