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

namespace App\DataFixtures;

use App\Carpool\Service\ProposalManager;
use App\DataFixtures\Service\SolidaryFixturesManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

class SolidaryFixtures extends Fixture implements FixtureGroupInterface
{
    public const BATCH = 50;                   // batch number for multi treatment

    private $fixturesManager;
    private $proposalManager;

    private $fixturesEnabled;
    private $fixturesClearBase;
    private $fixturesSolidary;

    public function __construct(
        SolidaryFixturesManager $fixturesManager,
        ProposalManager $proposalManager,
        bool $fixturesEnabled,
        bool $fixturesClearBase,
        bool $fixturesSolidary
    ) {
        $this->fixturesManager = $fixturesManager;
        $this->proposalManager = $proposalManager;
        $this->fixturesEnabled = $fixturesEnabled;
        $this->fixturesClearBase = $fixturesClearBase;
        $this->fixturesSolidary = $fixturesSolidary;
    }

    public function load(ObjectManager $manager)
    {
        if (!$this->fixturesEnabled) {
            echo 'Fixtures disabled' . PHP_EOL;

            exit;
        }

        // clear database
        if ($this->fixturesClearBase) {
            $this->fixturesManager->clearSolidaryData();
        }

        if ($this->fixturesSolidary) {
            $this->solidaryFixtures();
        }

        // // we compute the directions and default values for the generated proposals
        // echo "Creating directions and matchings... ";
        // $this->proposalManager->setDirectionsAndDefaultsForAllCriterias(self::BATCH);

        // // we generate the matchings
        // $this->proposalManager->createMatchingsForAllProposals();
        // echo "Done !" . PHP_EOL;
    }

    public static function getGroups(): array
    {
        return ['solidary'];
    }

    private function solidaryFixtures()
    {
        // Structures
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/Structures/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createStructure($tab);
                }
            }
        }

        // Link structures and territories
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/StructureTerritories/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createStructureTerritory($tab);
                }
            }
        }

        // Structure proofs
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/StructureProofs/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createStructureProof($tab);
                }
            }
        }

        // Needs
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/Needs/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createNeed($tab);
                }
            }
        }

        // Link structure and needs
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/StructureNeeds/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createStructureNeed($tab);
                }
            }
        }

        // Subjects
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/Subjects/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createSubject($tab);
                }
            }
        }

        // Operate (define where solidary managers can operate)
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/Operates/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createOperate($tab);
                }
            }
        }

        // SolidaryUsers
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/SolidaryUsers/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createSolidaryUser($tab);
                }
            }
        }

        // Link SolidaryUsers and Structures
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/SolidaryUserStructures/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createSolidaryUserStructure($tab);
                }
            }
        }

        // SolidaryUsers proofs
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Solidary/Proofs/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, 'r')) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    $this->fixturesManager->createProof($tab);
                }
            }
        }
    }
}
