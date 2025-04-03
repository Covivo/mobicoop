<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity\Stripe\Entity;

use App\Geography\Entity\Address;
use App\Payment\Exception\PaymentException;
use App\Service\Phone\PhoneService;
use App\User\Entity\User;

class AccountToken extends Token
{
    private const TYPE = 'account';
    private const BUSINESS_TYPE = 'individual';

    /**
     * @var string
     */
    private $business_type;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * @var bool
     */
    private $tos_shown_and_accepted;

    /**
     * @var Address
     * */
    private $address;

    /**
     * @var \DateTimeInterface
     * */
    private $birthDate;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $telephone;

    /**
     * @var string
     */
    private $validation_document_front_id;

    public function __construct(User $user, ?Address $address = null)
    {
        $this->business_type = self::BUSINESS_TYPE;
        $this->setFirstName($user->getGivenName());
        $this->setLastName($user->getFamilyName());
        $this->setAddress(!is_null($address) ? $address : $user->getHomeAddress());
        $this->setBirthDate($user->getBirthDate());
        $this->setEmail($user->getEmail());

        $phoneService = new PhoneService($user->getTelephone());
        $this->setTelephone($phoneService->getInternationalPhoneNumber());
    }

    public function getBusinessType(): string
    {
        return self::BUSINESS_TYPE;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getTosShownAndAccepted(): bool
    {
        return true;
    }

    public function setTosShownAndAccepted(bool $tos_shown_and_accepted): self
    {
        $this->tos_shown_and_accepted = $tos_shown_and_accepted;

        return $this;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getValidationDocumentFrontId(): ?string
    {
        return $this->validation_document_front_id;
    }

    public function setValidationDocumentFrontId(?string $validation_document_front_id): self
    {
        if (0 !== strpos($validation_document_front_id, 'file_')) {
            throw new PaymentException('validation_document_front_id must start with "file_"');
        }

        $this->validation_document_front_id = $validation_document_front_id;

        return $this;
    }

    public function buildBody(): array
    {
        $body = [
            self::TYPE => [
                'business_type' => $this->getBusinessType(),
                self::BUSINESS_TYPE => [
                    'first_name' => $this->getFirstName(),
                    'last_name' => $this->getLastName(),
                    'email' => $this->getEmail(),
                    'address' => $this->_buildAddress(),
                    'dob' => $this->_buildBirthDate(),
                    'phone' => $this->getTelephone(),
                ],
                'tos_shown_and_accepted' => $this->getTosShownAndAccepted(),
            ],
        ];

        if (!is_null($this->getValidationDocumentFrontId())) {
            $body[self::TYPE][self::BUSINESS_TYPE]['verification']['document'] = [
                'front' => $this->getValidationDocumentFrontId(),
            ];
        }

        return $body;
    }

    private function _buildBirthDate(): array
    {
        $birthDate = [];
        if (null !== $this->birthDate) {
            $birthDate = [
                'day' => $this->birthDate->format('d'),
                'month' => $this->birthDate->format('m'),
                'year' => $this->birthDate->format('Y'),
            ];
        }

        return $birthDate;
    }

    private function _buildAddress(): array
    {
        $address = [];
        if (
            '' !== $this->address->getStreetAddress()
            && '' !== $this->address->getStreet()
            && '' !== $this->address->getAddressLocality()
            && '' !== $this->address->getRegion()
            && '' !== $this->address->getPostalCode()
            && '' !== $this->address->getCountryCode()
        ) {
            $street = '';
            if ('' != $this->address->getStreetAddress()) {
                $street = $this->address->getStreetAddress();
            } else {
                $street = trim($this->address->getHouseNumber().' '.$this->address->getStreet());
            }
            $address = [
                'line1' => $street,
                'city' => $this->address->getAddressLocality(),
                'postal_code' => $this->address->getPostalCode(),
                'country' => substr($this->address->getCountryCode(), 0, 2),
            ];
        }

        return $address;
    }
}
