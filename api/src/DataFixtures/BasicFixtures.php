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

use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\ProposalManager;
use App\DataFixtures\Service\BasicFixturesManager;
use App\Geography\Service\TerritoryManager;
use App\Image\Service\ImageManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

class BasicFixtures extends Fixture implements FixtureGroupInterface
{
    public const BATCH = 50;                   // batch number for multi treatment

    private $fixturesManager;
    private $proposalManager;
    private $territoryManager;
    private $imageManager;

    private $fixturesEnabled;
    private $fixturesClearBase;
    private $fixturesBasic;
    private $matchingRepository;

    public function __construct(
        BasicFixturesManager $fixturesManager,
        ProposalManager $proposalManager,
        MatchingRepository $matchingRepository,
        TerritoryManager $territoryManager,
        ImageManager $imageManager,
        bool $fixturesEnabled,
        bool $fixturesClearBase,
        bool $fixturesBasic
    ) {
        $this->fixturesManager = $fixturesManager;
        $this->proposalManager = $proposalManager;
        $this->matchingRepository = $matchingRepository;
        $this->fixturesEnabled = $fixturesEnabled;
        $this->fixturesClearBase = $fixturesClearBase;
        $this->fixturesBasic = $fixturesBasic;
        $this->territoryManager = $territoryManager;
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager)
    {
        if (!$this->fixturesEnabled) {
            echo 'Fixtures disabled'.PHP_EOL;

            exit;
        }

        // clear database
        if ($this->fixturesClearBase) {
            $this->fixturesManager->clearBasicData();
        }

        if ($this->fixturesBasic) {
            // load icons infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Icons/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createIcons($tab);
                    }
                }
            }

            // load users info from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Users/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createUser($tab);
                    }
                }
            }

            // load ads info from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Ads/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the ad
                        if ($ad = $this->fixturesManager->createAd($tab)) {
                            $outwardProposal = $this->proposalManager->prepareProposal($this->proposalManager->get($ad->getId()));
                            if (!$ad->isOneWay()) {
                                $returnProposal = $this->proposalManager->prepareProposal($this->proposalManager->get($outwardProposal->getProposalLinked()->getId()));
                                $this->matchingRepository->linkRelatedMatchings($outwardProposal->getId());
                            }
                            if (Ad::ROLE_DRIVER_OR_PASSENGER == $ad->getRole()) {
                                // linking for the outward
                                $this->matchingRepository->linkOppositeMatchings($outwardProposal->getId());
                                if (!$ad->isOneWay()) {
                                    // linking for the return
                                    $this->matchingRepository->linkOppositeMatchings($returnProposal->getId());
                                }
                            }
                        }
                    }
                }
            }

            // load events info from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Events/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createEvent($tab);
                    }
                }
            }

            // load communities info from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Communities/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createCommunity($tab);
                    }
                }
            }

            // load communities users info from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/CommunityUsers/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createCommunityUser($tab);
                    }
                }
            }

            // Territories (direct SQL requests in the file because of geographic data)
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Territories/');
            $finder->name('*.sql');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while (!feof($file)) {
                        $this->fixturesManager->createTerritory(fgets($file));
                    }
                }
            }

            // load relayPointTypes infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/RelayPointTypes/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        $this->fixturesManager->createRelayPointType($tab);
                    }
                }
            }

            // load Images infos from csv file
            $finder = new Finder();
            $finder->in(__DIR__.'/Csv/Basic/Images/');
            $finder->name('*.csv');
            $finder->files();
            foreach ($finder as $file) {
                echo "Importing : {$file->getBasename()} ".PHP_EOL;
                if ($file = fopen($file, 'r')) {
                    while ($tab = fgetcsv($file, 4096, ';')) {
                        // create the image
                        $this->fixturesManager->createImage($tab);
                    }
                }
            }
        }

        // Link addresses and territories
        $this->territoryManager->linkNewAddressesWithTerritories();

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
