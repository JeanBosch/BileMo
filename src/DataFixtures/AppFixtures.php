<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Customer;
use App\Entity\Product;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {

        

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
        $user = new User();
        $user->setCompany($faker->company);
        $user->setEmail($faker->email);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setCreationDate($faker->dateTimeBetween('-6 months'));
        $manager->persist($user);
        }

        for ($i = 0; $i < 20; $i++) {

        $customer = new Customer();
        $customer->setEmail($faker->email);
        $customer->setName($faker->name);
        $customer->setCreationDate($faker->dateTimeBetween('-6 months'));
        $customer->setVendor($user);
        $manager->persist($customer);
        }

            for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($faker->name);
            $product->setDescription($faker->text);
            $product->setCreationDate($faker->dateTimeBetween('-6 months'));
            $product->setManufacturer("Apple");
            $product->setLength($faker->randomFloat(2, 0, 100));
            $product->setWidth($faker->randomFloat(2, 0, 100));
            $product->setWeight($faker->randomFloat(2, 0, 100));
            $product->setPrice($faker->randomFloat(2, 10, 100));
            $product->setImage($faker->imageUrl(640, 480, 'cats', true, 'Faker'));
            $manager->persist($product);
                
                }



       
            
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
