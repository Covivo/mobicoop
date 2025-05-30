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

namespace App\Carpool\Entity;

use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;

/**
 * A CarpoolExport item.
 *
 *  @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CarpoolExport
{
    public const DEFAULT_ID = 999999999999;

    public const ROLE_DRIVER = 1;
    public const ROLE_PASSENGER = 2;

    public const MODE_ONLINE = 1;
    public const MODE_DIRECT = 2;

    public const CERTIFICATION_A = 'A';
    public const CERTIFICATION_B = 'B';
    public const CERTIFICATION_C = 'C';

    public const CERTIFICATION_MISSING_ASK = 'Le RPC ne peut pas certifier la preuve de votre trajet, car votre annonce a été supprimée.';
    public const CERTIFICATION_UNDER_CHECKING = 'En cours de certification';
    public const CERTIFICATION_CANCELED = 'La certification a été annulé';
    public const CERTIFICATION_ERROR = 'Non certifié par le RPC';
    public const CERTIFICATION_NOT_SENT_MISSING_PHONE = 'Non envoyée car le numéro de téléphone n\'a pas été renseigné';

    /**
     * @var int the id of this carpoolExport item
     */
    private $id;

    /**
     * @var null|\DateTimeInterface the date of the carpool
     */
    private $date;

    /**
     * @var int the frequency (1 = driver; 2 = passenger)
     */
    private $role;

    /**
     * @var User The carpooler
     */
    private $carpooler;

    /**
     * @var null|string The origin of the carpool
     */
    private $pickUp;

    /**
     * @var null|string The destination of the carpool
     */
    private $dropOff;

    /**
     * @var null|string the amount for the carpool
     */
    private $amount;

    /**
     * @var null|int the mode of payment for the carpool
     */
    private $mode;

    /**
     * @var null|string the Certification of the payment for the carpool
     */
    private $certification;

    /**
     * @var null|int the distance in km of the carpool
     */
    private $distance;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(User $carpooler): self
    {
        $this->carpooler = $carpooler;

        return $this;
    }

    public function getPickUp(): ?string
    {
        return $this->pickUp;
    }

    public function setPickUp(?string $pickUp): self
    {
        $this->pickUp = $pickUp;

        return $this;
    }

    public function getDropOff(): ?string
    {
        return $this->dropOff;
    }

    public function setDropOff(?string $dropOff): self
    {
        $this->dropOff = $dropOff;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode)
    {
        $this->mode = $mode;
    }

    public function getCertification(): ?string
    {
        return $this->certification;
    }

    public function setCertification(?CarpoolItem $carpoolItem): self
    {
        if (!is_null($carpoolItem->getCarpoolProof())) {
            $this->_setCertificationAccordingToProofStatus($carpoolItem->getCarpoolProof());
        } else {
            $this->_setCertificationWithoutProof($carpoolItem);
        }

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance)
    {
        $this->distance = $distance;
    }

    private function _setCertificationWithoutProof(CarpoolItem $carpoolItem): self
    {
        if (is_null($carpoolItem->getAsk())) {
            $this->certification = self::CERTIFICATION_MISSING_ASK;
        }

        return $this;
    }

    private function _setCertificationAccordingToProofStatus(CarpoolProof $carpoolProof): self
    {
        switch ($carpoolProof->getStatus()) {
            case CarpoolProof::STATUS_PENDING:
            case CarpoolProof::STATUS_SENT:
            case CarpoolProof::STATUS_UNDER_CHECKING:
            case CarpoolProof::STATUS_RPC_NOT_REACHABLE:
                $this->certification = self::CERTIFICATION_UNDER_CHECKING;

                break;

            case CarpoolProof::STATUS_CANCELED:
            case CarpoolProof::STATUS_CANCELED_BY_OPERATOR:
                $this->certification = self::CERTIFICATION_CANCELED;

                break;

            case CarpoolProof::STATUS_VALIDATED:
                $this->certification = $carpoolProof->getType();

                break;

            case CarpoolProof::STATUS_NOT_SENT_MISSING_PHONE:
                $this->certification = self::CERTIFICATION_NOT_SENT_MISSING_PHONE;

                break;

            case CarpoolProof::STATUS_ERROR:
            case CarpoolProof::STATUS_ACQUISITION_ERROR:
            case CarpoolProof::STATUS_FRAUD_ERROR:
            case CarpoolProof::STATUS_EXPIRED:
            case CarpoolProof::STATUS_INVALID_CONCURRENT_SCHEDULES:
            case CarpoolProof::STATUS_INVALID_DUPLICATE_DEVICE:
            case CarpoolProof::STATUS_INVALID_SPLITTED_TRIP:
            case CarpoolProof::STATUS_DISTANCE_TOO_SHORT:
            case CarpoolProof::STATUS_CONCURRENT_DRIVER_JOURNEY:
            case CarpoolProof::STATUS_MAX_OPERATOR_TRIP_IDS:
            case CarpoolProof::STATUS_ANOMALY_ERROR:
            case CarpoolProof::STATUS_TERMS_VIOLATION_ERROR:
            case CarpoolProof::STATUS_VALIDATION_ERROR:
            case CarpoolProof::STATUS_IGNORED:
                $this->certification = self::CERTIFICATION_ERROR;

                break;
        }

        return $this;
    }
}
