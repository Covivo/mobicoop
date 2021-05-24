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

namespace App\Payment\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * A payment or a validation of a payment.
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *     },
 *     collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "post"={
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 *  @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PaymentPayment
{
    const DEFAULT_ID = 999999999999;

    const TYPE_PAY = 1;
    const TYPE_VALIDATE = 2;

    const MODE_ONLINE = 1;
    const MODE_DIRECT = 2;

    const STATUS_INITIATED = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;

    const ORIGIN_DESKTOP = 0;
    const ORIGIN_MOBILE = 1;
    const ORIGIN_MOBILE_SITE = 2;

    /**
     * @var int The id of this payment.
     * @Groups({"readPayment","writePayment"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int The payment type (1 = a payment to be made, 2 = a payment validation).
     * @Groups({"writePayment"})
     */
    private $type;

    /**
     * @var array|null The items concerned by the payment.
     * Each item of the array contains the :
     * - the id of the payment item
     * - the status (1 = realized, 2 = not realized, 3 = unpaid)
     * - the mode for the payment if type = 1 (1 = online, 2 = direct)
     * @Groups({"writePayment"})
     */
    private $items;

    /**
     * @var int The payment status (1 = success, 2 = failure).
     * @Groups({"readPayment"})
     */
    private $status;

    /**
     * @var int Origin of this payment
     * @Groups({"writePayment"})
     */
    private $origin;

    /**
     * @var string Secured form's url to process the electronic payement
     * @Groups({"readPayment"})
     */
    private $redirectUrl;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        $this->origin = self::ORIGIN_DESKTOP;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(int $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function setItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }
}
