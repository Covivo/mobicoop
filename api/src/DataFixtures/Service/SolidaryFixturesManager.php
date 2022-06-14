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
 */

namespace App\DataFixtures\Service;

use App\Geography\Entity\Address;
use App\Geography\Service\TerritoryManager;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Operate;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\Structure;
use App\Solidary\Entity\StructureProof;
use App\Solidary\Entity\Subject;
use App\Solidary\Repository\NeedRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Service\SolidaryManager;
use App\Solidary\Service\StructureManager;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Solidary Data fixtures manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryFixturesManager
{
    private const SOURCE_PROOF_PATH = __DIR__.'/../File/Solidary/Proofs/';
    private const DESTINATION_PROOF_PATH = __DIR__.'/../../../public/upload/solidary/proofs/';

    private $entityManager;
    private $userManager;
    private $territoryManager;
    private $needRepository;
    private $fixturesSolidary;
    private $structureManager;
    private $solidaryManager;
    private $solidaryUserRepository;
    private $solidaryUserStructureRepository;
    private $structureRepository;
    private $structureProofRepository;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        TerritoryManager $territoryManager,
        StructureManager $structureManager,
        SolidaryManager $solidaryManager,
        NeedRepository $needRepository,
        SolidaryUserRepository $solidaryUserRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        StructureRepository $structureRepository,
        StructureProofRepository $structureProofRepository,
        bool $fixturesSolidary
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->fixturesSolidary = $fixturesSolidary;
        $this->territoryManager = $territoryManager;
        $this->structureManager = $structureManager;
        $this->solidaryManager = $solidaryManager;
        $this->needRepository = $needRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->structureRepository = $structureRepository;
        $this->structureProofRepository = $structureProofRepository;
    }

    /**
     * Clear the database : remove all non essential data.
     */
    public function clearSolidaryData()
    {
        $conn = $this->entityManager->getConnection();
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        if ($this->fixturesSolidary) {
            echo 'Clearing Solidary database... '.PHP_EOL;
            $sql = '
            TRUNCATE `need`;
            TRUNCATE `operate`;
            TRUNCATE `proof`;
            TRUNCATE `solidary`;
            TRUNCATE `solidary_ask`;
            TRUNCATE `solidary_ask_history`;
            TRUNCATE `solidary_matching`;
            TRUNCATE `solidary_need`;
            TRUNCATE `solidary_solution`;
            TRUNCATE `solidary_user`;
            TRUNCATE `solidary_user_need`;
            TRUNCATE `solidary_user_structure`;
            TRUNCATE `structure`;
            TRUNCATE `structure_need`;
            TRUNCATE `structure_proof`;
            TRUNCATE `structure_territory`;
            TRUNCATE `subject`;
            ';

            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }

        $sql = '
        SET FOREIGN_KEY_CHECKS = 1;';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Create structure from an array.
     *
     * @param array $tab The array containing the structure (model in ../Csv/Solidary/Structures/structures.txt)
     */
    public function createStructure(array $tab)
    {
        echo 'Import Structure '.$tab[0].' - '.$tab[1].PHP_EOL;
        $structure = new Structure();
        $structure->setId($tab[0]);
        $structure->setName($tab[1]);
        $structure->setMMinTime(\Datetime::createFromFormat('H:i:s', $tab[2]));
        $structure->setMMaxTime(\Datetime::createFromFormat('H:i:s', $tab[3]));
        $structure->setAMinTime(\Datetime::createFromFormat('H:i:s', $tab[4]));
        $structure->setAMaxTime(\Datetime::createFromFormat('H:i:s', $tab[5]));
        $structure->setEMinTime(\Datetime::createFromFormat('H:i:s', $tab[6]));
        $structure->setEMaxTime(\Datetime::createFromFormat('H:i:s', $tab[7]));
        $structure->setMMon($tab[8]);
        $structure->setAMon($tab[9]);
        $structure->setEMon($tab[10]);
        $structure->setMTue($tab[11]);
        $structure->setATue($tab[12]);
        $structure->setETue($tab[13]);
        $structure->setMWed($tab[14]);
        $structure->setAWed($tab[15]);
        $structure->setEWed($tab[16]);
        $structure->setMThu($tab[17]);
        $structure->setAThu($tab[18]);
        $structure->setEThu($tab[19]);
        $structure->setMFri($tab[20]);
        $structure->setAFri($tab[21]);
        $structure->setEFri($tab[22]);
        $structure->setMSat($tab[23]);
        $structure->setASat($tab[24]);
        $structure->setESat($tab[25]);
        $structure->setMSun($tab[26]);
        $structure->setASun($tab[27]);
        $structure->setESun($tab[28]);
        $structure->setMMinRangeTime(\Datetime::createFromFormat('H:i:s', $tab[29]));
        $structure->setMMaxRangeTime(\Datetime::createFromFormat('H:i:s', $tab[30]));
        $structure->setAMinRangeTime(\Datetime::createFromFormat('H:i:s', $tab[31]));
        $structure->setAMaxRangeTime(\Datetime::createFromFormat('H:i:s', $tab[32]));
        $structure->setEMinRangeTime(\Datetime::createFromFormat('H:i:s', $tab[33]));
        $structure->setEMaxRangeTime(\Datetime::createFromFormat('H:i:s', $tab[34]));
        $structure->setEmail($tab[35]);
        $structure->setTelephone($tab[36]);
        $structure->setBeneficiaryAutoApproval($tab[37]);
        $structure->setMode($tab[38]);
        $this->entityManager->persist($structure);
        $this->entityManager->flush();
    }

    /**
     * Link structure and territory.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/StructureTerritories/structureTerritories.txt)
     */
    public function createStructureTerritory(array $tab)
    {
        echo 'Link structure '.$tab[0].' with territory : '.$tab[1].PHP_EOL;
        if ($structure = $this->structureManager->getStructure($tab[0])) {
            if ($territory = $this->territoryManager->getTerritory($tab[1])) {
                $structure->addTerritory($territory);
                $this->entityManager->persist($structure);
                $this->entityManager->flush();
            } else {
                echo 'Territory not found !'.PHP_EOL;
            }
        } else {
            echo 'Structure not found !'.PHP_EOL;
        }
    }

    /**
     * Create the structure proof.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/StructureTerritories/structureTerritories.txt)
     */
    public function createStructureProof(array $tab)
    {
        echo 'Import structureProof '.$tab[0].' '.$tab[1].PHP_EOL;
        if ($structure = $this->structureManager->getStructure($tab[0])) {
            $structureProof = new StructureProof();
            $structureProof->setStructure($structure);
            $structureProof->setLabel($tab[1]);
            $structureProof->setType($tab[2]);
            $structureProof->setPosition($tab[3]);
            $structureProof->setCheckbox($tab[4]);
            $structureProof->setInput($tab[5]);
            $structureProof->setSelectbox($tab[6]);
            $structureProof->setRadio($tab[7]);
            $structureProof->setOptions($tab[8]);
            $structureProof->setAcceptedValues($tab[9]);
            $structureProof->setFile($tab[10]);
            $structureProof->setMandatory($tab[11]);
            $structureProof->setActivated($tab[12]);
            $this->entityManager->persist($structureProof);
            $this->entityManager->flush();
        } else {
            echo 'Structure not found !'.PHP_EOL;
        }
    }

    /**
     * Create the need.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/Needs/needs.txt)
     */
    public function createNeed(array $tab)
    {
        echo 'Import need '.$tab[0].' '.$tab[2].PHP_EOL;
        $need = new Need();
        $need->setId($tab[0]);

        if ('' !== $tab[1]) {
            if (!is_null($solidary = $this->solidaryManager->getSolidary($tab[1]))) {
                $need->setSolidary($solidary);
            } else {
                echo 'Solidary not found !'.PHP_EOL;
            }
        }

        $need->setLabel($tab[2]);
        $need->setPrivate($tab[3]);
        $need->setLabelVolunteer($tab[4]);
        $this->entityManager->persist($need);
        $this->entityManager->flush();
    }

    /**
     * Link the structure and the need.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/StructureNeeds/structureNeeds.txt)
     */
    public function createStructureNeed(array $tab)
    {
        echo 'Link structure '.$tab[0].' with Need : '.$tab[1].PHP_EOL;
        if ($structure = $this->structureManager->getStructure($tab[0])) {
            if ($need = $this->needRepository->find($tab[1])) {
                $structure->addNeed($need);
                $this->entityManager->persist($structure);
                $this->entityManager->flush();
            } else {
                echo 'Need not found !'.PHP_EOL;
            }
        } else {
            echo 'Structure not found !'.PHP_EOL;
        }
    }

    /**
     * Create the subject.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/Subjects/subjects.txt)
     */
    public function createSubject(array $tab)
    {
        echo 'Import subject '.$tab[0].' for structure '.$tab[1].PHP_EOL;
        if ($structure = $this->structureManager->getStructure($tab[1])) {
            $subject = new Subject();
            $subject->setStructure($structure);
            $subject->setLabel($tab[0]);
            $structure->addSubject($subject);
            $this->entityManager->persist($structure);
            $this->entityManager->flush();
        } else {
            echo 'Structure not found !'.PHP_EOL;
        }
    }

    /**
     * Link the user and the structure in Operate.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/Operates/operates.txt)
     */
    public function createOperate(array $tab)
    {
        echo 'Link structure '.$tab[0].' with User : '.$tab[1].PHP_EOL;
        if ($structure = $this->structureManager->getStructure($tab[0])) {
            if ($user = $this->userManager->getUser($tab[1])) {
                $operate = new Operate();
                $operate->setStructure($structure);
                $operate->setUser($user);
                $this->entityManager->persist($operate);
                $this->entityManager->flush();
            } else {
                echo 'User not found !'.PHP_EOL;
            }
        } else {
            echo 'Structure not found !'.PHP_EOL;
        }
    }

    /**
     * Create a SolidaryUser.
     *
     * @param array $tab The array containing the solidaryUser (model in ../Csv/Solidary/SolidaryUsers/solidaryUsers.txt)
     */
    public function createSolidaryUser(array $tab)
    {
        echo 'SolidaryUser of User : '.$tab[39].PHP_EOL;
        if ($user = $this->userManager->getUser($tab[39])) {
            $solidaryUser = new SolidaryUser();

            // Address of the solidary User
            $address = new Address();
            $address->setHouseNumber($tab[0]);
            $address->setStreet($tab[1]);
            $address->setPostalCode($tab[2]);
            $address->setAddressLocality($tab[3]);
            $address->setAddressCountry($tab[4]);
            $address->setLatitude($tab[5]);
            $address->setLongitude($tab[6]);
            $solidaryUser->setAddress($address);

            $solidaryUser->setBeneficiary($tab[7]);
            $solidaryUser->setVolunteer($tab[8]);
            if ('' !== $tab[9]) {
                $solidaryUser->setMMinTime(\Datetime::createFromFormat('H:i:s', $tab[9]));
            }
            if ('' !== $tab[10]) {
                $solidaryUser->setMMaxTime(\Datetime::createFromFormat('H:i:s', $tab[10]));
            }
            if ('' !== $tab[11]) {
                $solidaryUser->setAMinTime(\Datetime::createFromFormat('H:i:s', $tab[11]));
            }
            if ('' !== $tab[12]) {
                $solidaryUser->setAMaxTime(\Datetime::createFromFormat('H:i:s', $tab[12]));
            }
            if ('' !== $tab[13]) {
                $solidaryUser->setEMinTime(\Datetime::createFromFormat('H:i:s', $tab[13]));
            }
            if ('' !== $tab[14]) {
                $solidaryUser->setEMaxTime(\Datetime::createFromFormat('H:i:s', $tab[14]));
            }

            if ('' !== $tab[15]) {
                $solidaryUser->setMMon($tab[15]);
            }
            if ('' !== $tab[16]) {
                $solidaryUser->setAMon($tab[16]);
            }
            if ('' !== $tab[17]) {
                $solidaryUser->setEMon($tab[17]);
            }
            if ('' !== $tab[18]) {
                $solidaryUser->setMTue($tab[18]);
            }
            if ('' !== $tab[19]) {
                $solidaryUser->setATue($tab[19]);
            }
            if ('' !== $tab[20]) {
                $solidaryUser->setETue($tab[20]);
            }
            if ('' !== $tab[21]) {
                $solidaryUser->setMWed($tab[21]);
            }
            if ('' !== $tab[22]) {
                $solidaryUser->setAWed($tab[22]);
            }
            if ('' !== $tab[23]) {
                $solidaryUser->setEWed($tab[23]);
            }
            if ('' !== $tab[24]) {
                $solidaryUser->setMThu($tab[24]);
            }
            if ('' !== $tab[25]) {
                $solidaryUser->setAThu($tab[25]);
            }
            if ('' !== $tab[26]) {
                $solidaryUser->setEThu($tab[26]);
            }
            if ('' !== $tab[27]) {
                $solidaryUser->setMFri($tab[27]);
            }
            if ('' !== $tab[28]) {
                $solidaryUser->setAFri($tab[28]);
            }
            if ('' !== $tab[29]) {
                $solidaryUser->setEFri($tab[29]);
            }
            if ('' !== $tab[30]) {
                $solidaryUser->setMSat($tab[30]);
            }
            if ('' !== $tab[31]) {
                $solidaryUser->setASat($tab[31]);
            }
            if ('' !== $tab[32]) {
                $solidaryUser->setESat($tab[32]);
            }
            if ('' !== $tab[33]) {
                $solidaryUser->setMSun($tab[33]);
            }
            if ('' !== $tab[34]) {
                $solidaryUser->setASun($tab[34]);
            }
            if ('' !== $tab[35]) {
                $solidaryUser->setESun($tab[35]);
            }

            if ('' !== $tab[36]) {
                $solidaryUser->setMaxDistance($tab[36]);
            }

            if ('' !== $tab[37]) {
                $solidaryUser->setVehicle($tab[37]);
            }
            $solidaryUser->setComment($tab[38]);

            $user->setSolidaryUser($solidaryUser);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            echo 'User not found !'.PHP_EOL;
        }
    }

    /**
     * Link SolidaryUsers and Structures.
     *
     * @param array $tab The array containing the links (model in ../Csv/Solidary/SolidaryUserStructures/solidaryUserStructures.txt)
     */
    public function createSolidaryUserStructure(array $tab)
    {
        echo 'Link SolidaryUser '.$tab[1].' with structure : '.$tab[2].PHP_EOL;
        if ($solidaryUser = $this->solidaryUserRepository->find($tab[1])) {
            if ($structure = $this->structureRepository->find($tab[2])) {
                $solidaryUserStructure = new SolidaryUserStructure();
                $solidaryUserStructure->setId($tab[0]);
                $solidaryUserStructure->setSolidaryUser($solidaryUser);
                $solidaryUserStructure->setStructure($structure);
                $solidaryUserStructure->setStatus($tab[3]);
                if ('' !== $tab[4]) {
                    $solidaryUserStructure->setAcceptedDate(\Datetime::createFromFormat('Y-m-d H:i:s', $tab[4]));
                }
                if ('' !== $tab[5]) {
                    $solidaryUserStructure->setRefusedDate(\Datetime::createFromFormat('Y-m-d H:i:s', $tab[5]));
                }
                $this->entityManager->persist($solidaryUserStructure);
                $this->entityManager->flush();
            } else {
                echo 'Structure not found !'.PHP_EOL;
            }
        } else {
            echo 'SolidaryUser not found !'.PHP_EOL;
        }
    }

    /**
     * Create the proofs.
     *
     * @param array $tab The array containing the proofs (model in ../Csv/Solidary/Proofs/proofs.txt)
     */
    public function createProof(array $tab)
    {
        echo 'Import proof for StructureProof '.$tab[0].' and SolidaryUserStructure '.$tab[1].PHP_EOL;
        $proof = new Proof();
        if ($structureProof = $this->structureProofRepository->find($tab[0])) {
            $proof->setStructureProof($structureProof);
        } else {
            echo 'StructureProof not found !'.PHP_EOL;

            return;
        }
        if ($solidaryUserStructure = $this->solidaryUserStructureRepository->find($tab[1])) {
            $proof->setSolidaryUserStructure($solidaryUserStructure);
        } else {
            echo 'SolidaryUserStructure not found !'.PHP_EOL;

            return;
        }
        $proof->setValue($tab[2]);
        /**
         * @var StructureProof $structureProof
         */
        if ($structureProof->isCheckbox()) {
            $proof->setValue(filter_var($tab[1], FILTER_VALIDATE_BOOLEAN));
        }

        if ($structureProof->isFile()) {
            if ('' !== $tab[3]) {
                $proof->setFileName($tab[3]);
            } else {
                echo 'Filename not found !'.PHP_EOL;

                return;
            }
            $file = self::SOURCE_PROOF_PATH.$tab[3];
            if (!is_file($file)) {
                echo 'File '.$file.' not found !'.PHP_EOL;

                return;
            }
            if ('' !== $tab[4]) {
                $proof->setOriginalName($tab[4]);
            }
            $infos = getimagesize($file);
            $proof->setMimeType($infos['mime']);
            $proof->setSize(filesize($file));
            if (!copy($file, self::DESTINATION_PROOF_PATH.$proof->getFileName())) {
                echo 'File copy failed !'.PHP_EOL;

                return;
            }
        }
        $this->entityManager->persist($proof);
        $this->entityManager->flush();
    }
}
