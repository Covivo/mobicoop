<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Match\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * An Mass.
 */
class Mass implements Resource
{
    const NB_WORKING_DAY = 228;
    const EARTH_CIRCUMFERENCE_IN_KILOMETERS = 40070;
    const FLAT_EARTH_CIRCUMFERENCE_IN_MILES = 78186;
    const AVERAGE_EARTH_MOON_DISTANCE_IN_KILOMETERS = 384400;
    const PARIS_NEW_YORK_CO2_IN_GRAM = 875700; // For 1 passenger


    /**
     * @var int The id of this mass.
     */
    private $id;

    /**
     * @var string|null The iri of this mass.
     */
    private $iri;

    /**
     * @var int The status of this import.
     */
    private $status;

    /**
     * @var string The final file name of the import.
     */
    private $fileName;

    /**
     * @var string The original file name of the import.
     */
    private $originalName;

    /**
     * @var int The size in bytes of the import.
     */
    private $size;

    /**
     * @var string The mime type of the import.
     */
    private $mimeType;

    /**
     * @var \DateTimeInterface Creation date of the import.
     *
     */
    private $createdDate;

    /**
     * @var User User that imports the file.
     */
    private $user;

    /**
     * @var \DateTimeInterface Analyzed date of the import.
     */
    private $analyzingDate;

    /**
     * @var \DateTimeInterface Analyze date of the import.
     */
    private $analyzedDate;

    /**
     * @var \DateTimeInterface Calculation date of the import.
     */
    private $calculationDate;

    /**
     * @var \DateTimeInterface Calculation date of the import.
     */
    private $calculatedDate;

    /**
     * @var File|null
     * @Assert\File(
     *     mimeTypes = {"text/csv", "text/plain", "application/zip", "application/xml", "text/xml", "application/json"},
     *     mimeTypesMessage = "Format de fichier invalide"
     *     )
     * @Groups({"post","put"})
     */
    private $file;

    /**
     * @var int|null The user id associated with the file.
     * @Groups({"post","put"})
     */
    private $userId;

    /**
     * @var array The errors.
     */
    private $errors;

    /**
     * @var array The persons.
     */
    private $persons;

    /**
     * @var array people's coordinates of this mass.
     */
    private $personsCoords;

    /**
     * @var array Working Places of this Mass
     */
    private $workingPlaces;

    /**
     * @var array Computed data of this mass.
     */
    private $computedData;

    /**
     * @var MassMatrix Matrix of carpools
     */
    private $massMatrix;

    public function __construct($id = null)
    {
        $this->id = $id;
        $this->errors = [];
        $this->persons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAnalyzedDate(): ?\DateTimeInterface
    {
        return $this->analyzedDate;
    }

    public function setAnalyzedDate(?\DateTimeInterface $analyzedDate): self
    {
        $this->analyzedDate = $analyzedDate;

        return $this;
    }

    public function getAnalyzingDate(): ?\DateTimeInterface
    {
        return $this->analyzingDate;
    }

    public function setAnalyzingDate(?\DateTimeInterface $analyzingDate): self
    {
        $this->analyzingDate = $analyzingDate;

        return $this;
    }

    public function getCalculationDate(): ?\DateTimeInterface
    {
        return $this->calculationDate;
    }

    public function setCalculationDate(?\DateTimeInterface $calculationDate): self
    {
        $this->calculationDate = $calculationDate;

        return $this;
    }

    public function getCalculatedDate(): ?\DateTimeInterface
    {
        return $this->calculatedDate;
    }

    public function setCalculatedDate(?\DateTimeInterface $calculatedDate): self
    {
        $this->calculatedDate = $calculatedDate;

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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function setErrors(?array $errors)
    {
        $this->errors = $errors;
    }

    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function addPerson(MassPerson $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons->add($person);
            $person->setMass($this);
        }

        return $this;
    }

    public function removePerson(MassPerson $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getMass() === $this) {
                $person->setMass(null);
            }
        }

        return $this;
    }

    public function getPersonsCoords(): ?array
    {
        return $this->personsCoords;
    }

    public function setPersonsCoords(?array $personsCoords)
    {
        $this->personsCoords = $personsCoords;
    }

    public function getWorkingPlaces(): ?array
    {
        return $this->workingPlaces;
    }

    public function setWorkingPlaces(array $workingplaces): self
    {
        foreach ($workingplaces as $key => $workingplace) {
            $workingplaces[$key]["address"] = $workingplace["houseNumber"] . " " . $workingplace["street"] . " " . $workingplace["postalCode"] . " " . $workingplace["addressLocality"];
        }

        $this->workingPlaces = $workingplaces;

        return $this;
    }

    public function addWorkingPlaces(array $workingplace): self
    {
        $workingplace["address"] = $workingplace["houseNumber"] . " " . $workingplace["street"] . " " . $workingplace["postalCode"] . " " . $workingplace["addressLocality"];

        if (!$this->workingPlaces->contains($workingplace)) {
            $this->workingPlaces->add($workingplace);
        }

        return $this;
    }

    public function getComputedData(): ?array
    {
        return $this->computedData;
    }

    public function setComputedData(?array $computedData)
    {
        $this->computedData = $computedData;
    }

    public function getMassMatrix(): ?MassMatrix
    {
        return $this->massMatrix;
    }

    public function setMassMatrix(?MassMatrix $massMatrix)
    {
        $this->massMatrix = $massMatrix;
    }
}
