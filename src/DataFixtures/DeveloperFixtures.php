<?php

namespace App\DataFixtures;

use App\Entity\Developer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DeveloperFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $developers = [
            ['name' => 'DEV1', 'efficiency' => 1],
            ['name' => 'DEV2', 'efficiency' => 2],
            ['name' => 'DEV3', 'efficiency' => 3],
            ['name' => 'DEV4', 'efficiency' => 4],
            ['name' => 'DEV5', 'efficiency' => 5],
        ];

        foreach ($developers as $data) {
            $developer = new Developer();
            $developer->setName($data['name']);
            $developer->setEfficiency($data['efficiency']);
            $manager->persist($developer);
        }

        $manager->flush();
    }
}
