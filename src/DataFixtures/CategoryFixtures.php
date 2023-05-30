<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{
    const CATEGORIES = [
        'Action',
        'Aventure',
        'Animation',
        'Comédie',
        'Drame',
        'Fantastique',
        'Horreur',
        'Policier',
        'Science-Fiction',
        'Thriller',
    ];

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager): void
    {
        foreach (self::CATEGORIES as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $slug = $this->slugger->slug($category->getName());
            $category->setSlug($slug);
            $this->addReference('categorie_' . $categoryName, $category);
            $manager->persist($category);
        }
        $manager->flush();
    }
}