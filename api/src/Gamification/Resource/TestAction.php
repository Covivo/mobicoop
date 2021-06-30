<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Gamification\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use App\Action\Entity\Action;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * Gamification : FOR DEVELOPPMENT PURPOSE ONLY
 * This Resource is usefull to trigger a log registration for a specific User and therefore check the gamification process
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readGamification"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeGamification"}}
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Gamification"}
 *               }
 *           },
 *           "post"={
 *              "security_post_denormalize"="is_granted('gamification_test_action',object)",
 *              "swagger_context" = {
 *                  "summary"="FOR DEVELOPPMENT PURPOSE ONLY",
 *                  "tags"={"Gamification"}
 *               }
 *           }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Gamification"}
 *              }
 *          }
 *      }
 * )
 *  @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class TestAction
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this TestAction.
     * @Assert\NotBlank
     * @Groups({"readGamification"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var Action The Action we want to test
     * @Assert\NotBlank
     * @Groups({"readGamification","writeGamification"})
     */
    private $action;

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

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }
}
