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
 */

namespace App\Rdex\Entity;

/**
 * An RDEX Vehicle.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexVehicle implements \JsonSerializable
{
    /**
     * @var string the image of the vehicle
     */
    private $vehicle_image;

    /**
     * @var string the model of the vehicle
     */
    private $model;

    /**
     * @var string the color of the vehicle
     */
    private $color;

    public function getVehicle_image(): string
    {
        return $this->vehicle_image;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $vehicle_image
     */
    public function setVehicle_image($vehicle_image)
    {
        $this->vehicle_image = $vehicle_image;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'vehicle_image' => $this->getVehicle_image(),
            'model' => $this->getModel(),
            'color' => $this->getColor(),
        ];
    }
}
