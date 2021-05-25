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

namespace App\DataProvider\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A MangoPay hook when there is a PayIn detected
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
 *          "mangoPayins"={
 *              "method"="GET",
 *              "path"="/mango-payins",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "mangoPayKYC"={
 *              "method"="GET",
 *              "path"="/mango-kyc",
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
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MangoPayHook extends Hook
{
    const PAYIN_SUCCEEDED = "PAYIN_NORMAL_SUCCEEDED";
    const PAYIN_FAILED = "PAYIN_NORMAL_FAILED";

    const VALIDATION_SUCCEEDED = "KYC_SUCCEEDED";
    const VALIDATION_FAILED = "KYC_FAILED";
    const VALIDATION_OUTDATED = "KYC_OUTDATED";
}
