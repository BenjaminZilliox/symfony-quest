<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Program;
use App\DataFixtures\CategoryFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\DataFixtures\UserFixtures;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $admin = $this->getReference('admin');
        $contributor = $this->getReference('contributor');
        for ($i = 0; $i < 10; $i++) {
            $program = new Program();
            $program->setTitle($faker->sentence());
            $slug = $this->slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $program->setSynopsis($faker->paragraphs(3, true));
            $program->setPoster('https://i.pinimg.com/736x/b7/49/74/b74974c3c4728ce2063d9b9617216814.jpg');
            $program->setCountry($faker->country());
            $program->setYear($faker->year());
            $randomCategoryKey = array_rand(CategoryFixtures::CATEGORIES);
            $categoryName = CategoryFixtures::CATEGORIES[$randomCategoryKey];
            $program->setCategory($this->getReference('categorie_' . $categoryName));
            $this->addReference('program_' . $i, $program);
            $user = $i % 2 === 0 ? $contributor : $admin;
            $program->setOwner($user);
            $manager->persist($program);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}