<?php

namespace App\DataFixtures;

use App\Entity\Classroom;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $classroom1 = new Classroom();
        $classroom1->setName('Salle 1');
        $classroom1->setNumber(1);
        $classroom1->setSize(20);
        $manager->persist($classroom1);

        $classroom2 = new Classroom();
        $classroom2->setName('Salle 2');
        $classroom2->setNumber(2);
        $classroom2->setSize(25);
        $manager->persist($classroom2);

        $manager->flush();
    }
}
