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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * A Bank account
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankAccount implements ResourceInterface, \JsonSerializable
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    const VALIDATION_PENDING = 0;
    const VALIDATION_VALIDATED = 1;
    const VALIDATION_REJECTED = 2;
    const VALIDATION_OUTDATED = 3;

    const OUT_OF_DATE = 1;
    const UNDERAGE_PERSON = 2;
    const DOCUMENT_FALSIFIED = 3;
    const DOCUMENT_MISSING = 4;
    const DOCUMENT_HAS_EXPIRED = 5;
    const DOCUMENT_NOT_ACCEPTED = 6;
    const DOCUMENT_DO_NOT_MATCH_USER_DATA = 7;
    const DOCUMENT_UNREADABLE = 8;
    const DOCUMENT_INCOMPLETE = 9;

    /**
     * @var int The id of this bank account
     */
    private $id;

    /**
     * @var string|null The iri of this bank account.
     *
     */
    private $iri;

    /**
     * @var string|null The litteral name of the user owning this bank account
     */
    private $userLitteral;

    /**
     * @var Address|null The address linked to this bank account
     *
     * @Groups({"post"})
     */
    private $address;
    
    /**
     * @var string The iban number of this bank account
     *
     * @Assert\NotBlank
     * @Assert\Iban
     * @Groups({"post"})
     */
    private $iban;

    /**
     * @var string The bic number of this bank account
     *
     * @Assert\NotBlank
     * @Assert\Bic
     * @Groups({"post"})
     */
    private $bic;

    /**
     * @var string|null A comment for this bank account
     *
     * @Groups({"post"})
     */
    private $comment;

    /**
     * @var int The status of this bank account (0 : Inactive, 1 : Active)
     *
     * @Groups({"post"})
     */
    private $status;

    /**
     * @var int The validation status of this bank account (0 : pending, 1 : validated, 2 : rejected, 3 : outdated)
     */
    private $validationStatus;

    /**
     * @var int The reason why the identity document associated to the bankaccount is not validated
     *
     */
    private $refusalReason;

    /**
     * @var \DateTimeInterface Date when the validation has been asked to the payment provider
     */
    private $validationAskedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been granted by the payment provider
     */
    private $validatedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been declared outdated by the payment provider
     */
    private $validationOutdatedDate;

    /**
     * @var \DateTimeInterface Creation date.
     */
    private $createdDate;

    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/bank_accounts/".$id);
        }
        $this->images = new ArrayCollection();
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

    public function getUserLitteral(): ?String
    {
        return $this->userLitteral;
    }

    public function setUserLitteral(?String $userLitteral)
    {
        $this->userLitteral = $userLitteral;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address)
    {
        $this->address = $address;
    }

    public function getIban(): ?String
    {
        return $this->iban;
    }

    public function setIban(?String $iban)
    {
        $this->iban = $iban;
    }

    public function getBic(): ?String
    {
        return $this->bic;
    }

    public function setBic(?String $bic)
    {
        $this->bic = $bic;
    }

    public function getComment(): ?String
    {
        return $this->comment;
    }

    public function setComment(?String $comment)
    {
        $this->comment = $comment;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status)
    {
        $this->status = $status;
    }

    public function getValidationStatus(): ?int
    {
        return $this->validationStatus;
    }

    public function getRefusalReason(): ?int
    {
        return $this->refusalReason;
    }

    public function setRefusalReason(?int $refusalReason)
    {
        $this->refusalReason = $refusalReason;
    }

    public function getValidationAskedDate(): ?\DateTimeInterface
    {
        return $this->validationAskedDate;
    }

    public function setValidationAskedDate(?\DateTimeInterface $validationAskedDate): self
    {
        $this->validationAskedDate = $validationAskedDate;

        return $this;
    }
    
    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validatedDate;
    }

    public function setValidatedDate(?\DateTimeInterface $validatedDate): self
    {
        $this->validatedDate = $validatedDate;

        return $this;
    }

    public function getValidationOutdatedDate(): ?\DateTimeInterface
    {
        return $this->validationOutdatedDate;
    }

    public function setValidationOutdatedDate(?\DateTimeInterface $validationOutdatedDate): self
    {
        $this->validationOutdatedDate = $validationOutdatedDate;

        return $this;
    }

    public function setValidationStatus(?int $validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate)
    {
        $this->createdDate = $createdDate;
    }

    public function jsonSerialize()
    {
        return
            [
                'id'                            => $this->getId(),
                'iri'                           => $this->getIri(),
                'userLitteral'                  => $this->getUserLitteral(),
                'address'                       => $this->getAddress(),
                'iban'                          => $this->getIban(),
                'bic'                           => $this->getBic(),
                'comment'                       => $this->getComment(),
                'status'                        => $this->getStatus(),
                'validationStatus'              => $this->getValidationStatus(),
                'refusalReason'                 => $this->getRefusalReason(),
                'validationAskedDate'           => $this->getValidationAskedDate(),
                'validatedDate'                 => $this->getValidatedDate(),
                'validationOutdatedDate'        => $this->getValidationOutdatedDate(),
                'createdDate'                   => $this->getCreatedDate()
            ];
    }
}
