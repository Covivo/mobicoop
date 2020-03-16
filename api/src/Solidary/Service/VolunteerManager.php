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

namespace App\Solidary\Service;

use App\Solidary\Entity\Volunteer;
use App\Solidary\Entity\Exposed\Volunteer as ExposedVolunteer;

class VolunteerManager
{
    /**
     * Create a Volunteer from an ExposedVolunteer with the User account if necessary
     *
     * @param ExposedVolunteer $exposedVolunteer
     * @return ExposedVolunteer|null
     */
    public function createVolunteer(ExposedVolunteer $exposedVolunteer)
    {
        echo "nice";
        die;
//        $volunteer->setUser($this->userManager->prepareUser($volunteer->getUser(), true));

        return null;
    }
}
