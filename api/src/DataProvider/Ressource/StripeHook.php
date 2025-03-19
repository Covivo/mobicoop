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

namespace App\DataProvider\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use App\DataProvider\DataPersister\StripeHookDataPersister;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Stripe hook when there is a PayIn or KYC event is detected.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "stripeHook"={
 *              "method"="POST",
 *              "path"="/stripe-hook",
 *              "deserialize"=false,
 *              "controller"=StripeHookDataPersister::class,
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StripeHook extends Hook
{
    public const TYPE_ACCOUNT_UPDATED = 'account.updated';
    public const TYPE_PAYMENT_SUCCEEDED = 'checkout.session.completed';

    public const VALIDATION_SUCCEEDED = 'verified';
    public const VALIDATION_PENDING = 'pending';
    public const VALIDATION_FAILED = 'unverified';
}
