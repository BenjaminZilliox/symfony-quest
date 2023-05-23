<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $categories = ['Fantastique', 'Horreur', 'Action', 'Aventure', 'Animation'];
            $randomCategory = $categories[array_rand($categories)];
            $program = new Program();
            $program->setTitle('Program ' . $i);
            $program->setSynopsis('Synopsis ' . $i);
            $program->setCategory($this->getReference('category_' . $randomCategory));
            $manager->persist($program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont ProgramFixtures dépend
        return [
            CategoryFixtures::class,
        ];
    }
}
