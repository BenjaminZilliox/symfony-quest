<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Season;
use App\DataFixtures\ProgramFixtures;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 10; $i++) {
            $programReference = 'program_' . $i;
            for ($j = 0; $j < 5; $j++) {
                $season = new Season();
                $season->setNumber($j + 1);
                $slug = $this->slugger->slug($season->getNumber());
                $season->setSlug($slug);
                $season->setYear($faker->year());
                $season->setDescription($faker->paragraphs(3, true));
                $season->setProgram($this->getReference($programReference));
                $this->addReference('season_' . $i . '_' . $j, $season);
                $manager->persist($season);
            }
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            ProgramFixtures::class,
        ];
    }
}