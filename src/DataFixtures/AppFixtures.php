<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture implements FixtureInterface
{
    protected $faker;
    private static $postImage = [
        'com_co_thit.jpg',
        'lu_lut.jpeg',
        'secherresse.jpeg',
    ];
    public function load(ObjectManager $manager)
    {

        $this->faker = Faker\Factory::create('fr_FR');
        // $product = new Product();
        // $manager->persist($product);
        // create 20 products! Bam!
        // un-comment the line bellow to add new product on test

        for ($i = 0; $i < 20; $i++) {
            $product = new Post();
            $product->setTitle($this->faker->text(50));
            $title = $product->getTitle();
           // $product->setSlug(str_replace(' ', '-', $title));
            $product->setNumberParticipant(rand(1,120));
            $product->setAuthor($this->faker->name);
            $product->setContent($this->faker->text);
            $product->setPublishedAt($this->faker->dateTimeBetween('-100 days','now'));
            $product->setImageFilename($this->faker->randomElement(self::$postImage));
            $manager->persist($product);


            $comment1 = new Comment();
        $comment1->setAuthorName($this->faker->name);
        $comment1->setContent($this->faker->text);
        $comment1->setPost($product);
       $comment1->setCreatedAt($this->faker->dateTimeBetween('-100 days','now'));
            $manager->persist($comment1);

            $comment2 = new Comment();
            $comment2->setAuthorName($this->faker->name);
            $comment2->setContent($this->faker->text);
            $comment2->setPost($product);
          $comment2->setCreatedAt($this->faker->dateTimeBetween('-100 days','now'));
            $manager->persist($comment2);


        $manager->flush();
        }


    }
}
