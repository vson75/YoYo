<?php

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    protected $faker;
    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->faker = Faker\Factory::create('fr_FR');
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail("yoyo".$i."@gmail.com");
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setPassword($this->passwordEncoder->encodePassword($user,'123'));
            $manager->persist($user);
            $manager->flush();
        }

        for ($i =0;$i<3;$i++){
            $user = new User();
            $user->setEmail("admin".$i."@gmail.com");
            $user->setFirstname($this->faker->firstName);
            $user->setLastname($this->faker->lastName);
            $user->setPassword($this->passwordEncoder->encodePassword($user,'123'));
            $user->setRoles(['ROLE_ADMIN']);
            $manager->persist($user);
            $manager->flush();
        }

        $manager->flush();
    }
}
