<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class TagFixture extends Fixture
{
    protected $faker;
    public function load(ObjectManager $manager)
    {
        $this->faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 20; $i++) {
            $tag = new Tag();
            $tag->setNameTag($this->faker->realText(20));
            $tag->setCreatedAt($this->faker->dateTimeBetween('-100 days','now'));
            $tag->setUpdatedAt($this->faker->dateTimeBetween('-100 days','now'));
            $manager->persist($tag);
            $manager->flush();
        }
    }
}
