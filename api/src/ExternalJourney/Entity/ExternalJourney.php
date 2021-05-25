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

namespace App\ExternalJourney\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * An external carpool journey provided by a partner provider.
 *
 * @ApiResource(
 *     collectionOperations={
 *     "get"={
 *      "normalization_context"={"groups"={"externalJourney"}},
 *      "security"="is_granted('external_journey_list',object)",
 *      "swagger_context"={
 *           "tags"={"Carpool"},
 *           "parameters"={
 *              {
 *                  "name" = "driver",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "1 if you want to display drivers journeys, 0 instead"
 *              },
 *              {
 *                  "name" = "passenger",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "1 if you want to display passengers journeys, 0 instead"
 *              },
 *              {
 *                  "name" = "from_latitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Latitude of the origin point"
 *              },
 *              {
 *                  "name" = "from_longitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Longitude of the origin point"
 *              },
 *              {
 *                  "name" = "to_latitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Latitude of the destination point"
 *              },
 *              {
 *                  "name" = "to_longitude",
 *                  "in" = "query",
 *                  "required" = "true",
 *                  "type" = "string",
 *                  "description" = "Longitude of the destination point"
 *              },
 *              {
 *                  "name" = "rawJson",
 *                  "in" = "query",
 *                  "required" = "false",
 *                  "type" = "string",
 *                  "description" = "If set to 1, this return the raw RDEX format. Otherwise it's returning an array of Carpool Result"
 *              }
 *           }
 *      }
 *    }
 * },
 *      itemOperations={
 *          "get" = {
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalJourney
{
    // No field because this ressource is only used to make api call.
    // Every parameters are passed in GET params in the url. The body is empty.
    // The answer is a collection of App\Carpool\Entity\Results or a raw Json depending of the rawJson parameter.
}
