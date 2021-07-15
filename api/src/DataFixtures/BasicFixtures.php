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

namespace App\DataFixtures;

use App\Carpool\Service\ProposalManager;
use App\DataFixtures\Service\BasicFixturesManager;
use App\Geography\Service\TerritoryManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Finder\Finder;
use App\Image\Service\ImageManager;

class BasicFixtures extends Fixture implements FixtureGroupInterface
{
    const BATCH = 50;                   // batch number for multi treatment

    private $fixturesManager;
    private $proposalManager;
    private $territoryManager;
    private $imageManager;

    private $fixturesEnabled;
    private $fixturesClearBase;
    private $fixturesBasic;

    public function __construct(
        BasicFixturesManager $fixturesManager,
        ProposalManager $proposalManager,
        TerritoryManager $territoryManager,
        ImageManager $imageManager,
        bool $fixturesEnabled,
        bool $fixturesClearBase,
        bool $fixturesBasic
    ) {
        $this->fixturesManager = $fixturesManager;
        $this->proposalManager = $proposalManager;
        $this->fixturesEnabled = $fixturesEnabled;
        $this->fixturesClearBase = $fixturesClearBase;
        $this->fixturesBasic = $fixturesBasic;
        $this->territoryManager = $territoryManager;
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        if (!$this->fixturesEnabled) {
            echo "Fixtures disabled". PHP_EOL;
            exit;
        }
        
        // clear database
        if ($this->fixturesClearBase) {
            $this->fixturesManager->clearBasicData();
        }

        if ($this->fixturesBasic) {

            // load icons infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Icons/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createIcons($tab);
                    }
                }
            }

            // load users info from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Users/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the user
                        $this->fixturesManager->createUser($tab);
                    }
                }
            }

            // load ads info from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Ads/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the ad
                        if ($ad = $this->fixturesManager->createAd($tab)) {
                            $this->proposalManager->prepareProposal($this->proposalManager->get($ad->getId()), false);
                        }
                    }
                }
            }

            // load events info from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Events/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the event
                        $this->fixturesManager->createEvent($tab);
                    }
                }
            }

            // load communities info from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Communities/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community
                        $this->fixturesManager->createCommunity($tab);
                    }
                }
            }

            // load communities users info from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/CommunityUsers/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createCommunityUser($tab);
                    }
                }
            }

            // Territories (direct SQL requests in the file because of geographic data)
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Territories/');
            $finder->name('*.sql');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while (!feof($file)) {
                        // create the community user
                        $this->fixturesManager->createTerritories(fgets($file));
                    }
                }
            }

            // load relayPointTypes infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/RelayPointTypes/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createRelayPointTypes($tab);
                    }
                }
            }

            // load Badges infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Badges/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createBadges($tab);
                    }
                }
            }

            // load SequenceItems infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/SequenceItems/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createSequenceItems($tab);
                    }
                }
            }

            // load Images infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__ . '/Csv/Basic/Images/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} " . PHP_EOL;
                if ($file = fopen($file, "r")) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the community user
                        $this->fixturesManager->createImages($tab);
                    }
                }
            }
        }
        
        // Link addresses and territories
        $this->territoryManager->updateAddressesAndDirections();

        // // we compute the directions and default values for the generated proposals
        // echo "Creating directions and matchings... ";
        // $this->proposalManager->setDirectionsAndDefaultsForAllCriterias(self::BATCH);

        // // we generate the matchings
        // $this->proposalManager->createMatchingsForAllProposals();
        // echo "Done !" . PHP_EOL;
    }
    
    public static function getGroups(): array
    {
        return ['basic'];
    }
}
