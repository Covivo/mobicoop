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

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * Carpooling : an effective journey.
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 * @ORM\Entity
 * @ORM\Table(indexes={
 *  @ORM\Index(name="IDX_ORIGIN", columns={"origin"}),
 *  @ORM\Index(name="IDX_DESTINATION", columns={"destination"}),
 *  @ORM\Index(name="IDX_ORIGIN_DESTINATION", columns={"origin","destination"})
 * })
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readJourney"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeJourney"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "cities"={
 *              "method"="GET",
 *              "path"="/journeys/cities",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "popularHome"={
 *              "method"="GET",
 *              "path"="/journeys/popular/home",
 *              "normalization_context"={"groups"={"readPopularJourney"}},
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "popular"={
 *              "method"="GET",
 *              "path"="/journeys/popular",
 *              "normalization_context"={"groups"={"readPopularJourney"}},
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "origin"={
 *              "method"="GET",
 *              "path"="/journeys/origin/{origin}",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "destinations"={
 *              "method"="GET",
 *              "path"="/journeys/destinations/{origin}",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "destination"={
 *              "method"="GET",
 *              "path"="/journeys/destination/{destination}",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "origins"={
 *              "method"="GET",
 *              "path"="/journeys/origins/{destination}",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "originDestination"={
 *              "method"="GET",
 *              "path"="/journeys/origin/{origin}/destination/{destination}",
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *          "carpools"={
 *              "method"="POST",
 *              "path"="/journeys/carpools",
 *              "normalization_context"={"groups"={"journeyCarpools"}},
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool Summary"}
 *              }
 *          },
 *      }
 * )
 * @ApiFilter(SearchFilter::class, properties={"origin":"partial", "destination":"partial"})
 * @ApiFilter(NumericFilter::class, properties={"frequency"})
 * @ApiFilter(DateFilter::class, properties={"fromDate": DateFilter::EXCLUDE_NULL,"toDate": DateFilter::EXCLUDE_NULL})
 * @ApiFilter(OrderFilter::class, properties={"fromDate", "origin", "destination"}, arguments={"orderParameterName"="order"})
 */
class Journey
{
    const DEFAULT_ID = 999999999999;
    
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_DRIVER_OR_PASSENGER = 3;

    const POPULAR_RANDOMIZATION_FACTOR = 5; // Used to shuffle the results for popular Journeys

    /**
     * @var int The id of this journey.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readJourney"})
     */
    private $id;

    /**
     * @var int The proposal id for this journey
     * @ORM\Column(type="integer")
     * @Groups({"readJourney","writeJourney","journeyCarpools"})
     */
    private $proposalId;

    /**
     * @var int The user id for this journey
     * @ORM\Column(type="integer")
     * @Groups({"readJourney"})
     */
    private $userId;

    /**
     * @var string|null The name of the user.
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"readJourney"})
     */
    private $userName;

    /**
     * @var int|null The age of the user
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readJourney"})
     */
    private $age;
    
    /**
     * @var string The origin of the journey
     * @ORM\Column(type="string")
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $origin;

    /**
     * @var float|null The latitude of the origin.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $latitudeOrigin;

    /**
     * @var float|null The longitude of the origin.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $longitudeOrigin;

    /**
     * @var string The destination of the journey
     * @ORM\Column(type="string")
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $destination;

    /**
     * @var float|null The latitude of the destination.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $latitudeDestination;

    /**
     * @var float|null The longitude of the destination.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"readJourney", "readPopularJourney"})
     */
    private $longitudeDestination;

    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular).
     * @ORM\Column(type="smallint")
     * @Groups({"readJourney"})
     */
    private $frequency;

    /**
     * @var int The proposal type (1 = oneway; 2 = return trip).
     * @ORM\Column(type="smallint")
     * @Groups({"readJourney"})
     */
    private $type;

    /**
     * @var int The role for this journey (1 = driver; 2 = passenger; 3 = driver or passenger).
     * @ORM\Column(type="smallint")
     * @Groups({"readJourney"})
     */
    private $role;

    /**
     * @var \DateTimeInterface The starting date.
     *
     * @ORM\Column(type="date")
     * @Groups({"readJourney"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface The end date.
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"readJourney"})
     */
    private $toDate;

    /**
     * @var \DateTimeInterface|null The starting time for a punctual journey.
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"readJourney"})
     */
    private $time;

    /**
     * @var string|null The json representation of the possible days for a regular journey.
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"readJourney"})
     */
    private $days;

    /**
     * @var string|null The json representation of the outward times for a regular journey.
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"readJourney"})
     */
    private $outwardTimes;

    /**
     * @var string|null The json representation of the return times for a regular journey.
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"readJourney"})
     */
    private $returnTimes;


    /**
     * @var int|null The number of occurences of this journey (for Popular Journey only)
    * @Groups({"readPopularJourney"})
    */
    private $occurences;

    /**
     * @var \DateTimeInterface Creation date of the journey.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"readJourney"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the journey.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    public function __construct($id = null)
    {
        if (is_null($id)) {
            $this->id = self::DEFAULT_ID;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProposalId(): int
    {
        return $this->proposalId;
    }
    
    public function setProposalId(int $proposalId): self
    {
        $this->proposalId = $proposalId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }
    
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }
    
    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }
    
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getLatitudeOrigin()
    {
        return $this->latitudeOrigin;
    }

    public function setLatitudeOrigin($latitudeOrigin)
    {
        $this->latitudeOrigin = $latitudeOrigin;
    }

    public function getLongitudeOrigin()
    {
        return $this->longitudeOrigin;
    }

    public function setLongitudeOrigin($longitudeOrigin)
    {
        $this->longitudeOrigin = $longitudeOrigin;
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

    public function getLatitudeDestination()
    {
        return $this->latitudeDestination;
    }

    public function setLatitudeDestination($latitudeDestination)
    {
        $this->latitudeDestination = $latitudeDestination;
    }

    public function getLongitudeDestination()
    {
        return $this->longitudeDestination;
    }

    public function setLongitudeDestination($longitudeDestination)
    {
        $this->longitudeDestination = $longitudeDestination;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        if ($this->time) {
            return \DateTime::createFromFormat('His', $this->time->format('His'));
        }
        return null;
    }

    public function setTime(?\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getDays(): ?string
    {
        return $this->days;
    }

    public function setDays(?string $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getOutwardTimes(): ?string
    {
        return $this->outwardTimes;
    }

    public function setOutwardTimes(?string $outwardTimes): self
    {
        $this->outwardTimes = $outwardTimes;

        return $this;
    }

    public function getReturnTimes(): ?string
    {
        return $this->returnTimes;
    }

    public function setReturnTimes(?string $returnTimes): self
    {
        $this->returnTimes = $returnTimes;

        return $this;
    }

    public function getOccurences(): ?int
    {
        return $this->occurences;
    }

    public function setOccurences(int $occurences): self
    {
        $this->occurences = $occurences;

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
}
