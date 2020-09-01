<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Finder\Finder;

class BasicFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        // Bundle to manage file and directories
        $finder = new Finder();
        $finder->in(__DIR__ . '/Sql/Basic');
        $finder->name('*.sql');
        $finder->files();
        $finder->sortByName();

        foreach ($finder as $file) {
            print "Importing: {$file->getBasename()} " . PHP_EOL;

            $sql = $file->getContents();

            $manager->getConnection()->exec($sql);  // Execute native SQL

            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['basic'];
    }
}
