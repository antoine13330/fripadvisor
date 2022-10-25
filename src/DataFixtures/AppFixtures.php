<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Shop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    /**
     * Hash le password
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher) {
        $this->faker = Factory::create("fr_FR");
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        //Authenticated admin
        $adminUser = new User();
        $password = "password";
        $adminUser->setUsername('admin')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->userPasswordHasher->hashPassword($adminUser, $password));
        $manager->persist($adminUser);

        //Authenticated users
        for($i = 0; $i < 5; $i++) {
            $userUser = new User();
            $password = $this->faker->password(2, 6);
            $userUser->setUsername($this->faker->userName() . '@' . $password)
            ->setRoles(["ROLE_USER"])
            ->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
            $manager->persist($userUser);
        }

        $tableauShops = [];

        for($i = 0; $i < 5;$i++) {
            $shop = new Shop();
            $tableauShops[] = $shop;
            $shop->setName($this->faker->company())
                ->setPoastalCode($this->faker->streetAddress())
                ->setLocation($this->faker->streetAddress())
                ->setSatus(true);

            $manager->persist($shop);
        }

        $tableauCategory = [];

        for($i = 0; $i < 5;$i++) {
            $category = new Category();
            $category->setName($this->faker->word())
                ->setStatus(true);

            $manager->persist($category);
            $tableauCategory[] = $category;
        }

        for($i = 0; $i < 5;$i++) {
            $product = new Product();
            $randomCategory = array_rand($tableauCategory, 1);
            $randomShp = array_rand($tableauShops, 1);
            $product->setPrice($this->faker->word())
                ->setStatus(true);

            $manager->persist($product);

        }

        $manager->flush();
    }
}
