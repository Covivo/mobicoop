<?php
/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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


namespace Mobicoop\Bundle\MobicoopBundle\Solidary\Controller;

use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Solidary;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\SolidaryManager;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\StructureManager;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\SubjectManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SolidaryController extends AbstractController
{
    use HydraControllerTrait;

    /**
     *
     * @param StructureManager $structureManager
     * @param SubjectManager $subjectManager
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(StructureManager $structureManager, SubjectManager $subjectManager)
    {
        $structures = $structureManager->getStructures();
        $subjects = $subjectManager->getSubjects();
        
        return $this->render(
            '@Mobicoop/solidary/solidary.html.twig',
            [
                "subjects" => $subjects,
                "structures" => $structures
            ]
        );
    }

    public function solidaryCreate(Request $request, SolidaryManager $solidaryManager, UserManager $userManager)
    {
        $solidary = new Solidary();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            
            dump($data);
            die;
        }
    }
}
