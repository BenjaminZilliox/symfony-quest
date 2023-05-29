<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Program;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $program = new Program();
            $program->setTitle($faker->sentence());
            $program->setSynopsis($faker->paragraphs(3, true));
            $program->setPoster('https://i.pinimg.com/736x/b7/49/74/b74974c3c4728ce2063d9b9617216814.jpg');
            $program->setCountry($faker->country());
            $program->setYear($faker->year());
            $randomCategoryKey = array_rand(CategoryFixtures::CATEGORIES);
            $categoryName = CategoryFixtures::CATEGORIES[$randomCategoryKey];
            $program->setCategory($this->getReference('categorie_' . $categoryName));
            $this->addReference('program_' . $i, $program);
            $manager->persist($program);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}