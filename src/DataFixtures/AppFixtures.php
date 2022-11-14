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
use App\Repository\ShopRepository;
use App\Repository\CategoryRepository;
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

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, ShopRepository $shopRepository, CategoryRepository $categoryRepository) {
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

        //shops
        for($i = 0; $i < 5;$i++) {
            $shop = new Shop();

            $shop->setName($this->faker->company())
                ->setPoastalCode($this->faker->streetAddress())
                ->setLocation($this->faker->streetAddress())
                ->setSatus(true);

            $manager->persist($shop);

            //categories
            for($j = 0; $j < 5;$j++) {
                $category = new Category();
                $category->setName($this->faker->word())
                    ->setType($this->faker->word())
                    ->setStatus(true);

                $manager->persist($category);

                //products
                for($k = 0; $k < 5;$k++) {
                    $product = new Product();

                    $product->setName($this->faker->word())
                        ->setPrice($this->faker->numberBetween(5, 200))
                        ->setSize($this->faker->numberBetween(20, 50))
                        ->setStock($this->faker->numberBetween(0, 100))
                        ->setIdShop($shop)
                        ->addIdCategory($category)
                        ->setStatus(true);
                    $manager->persist($product);
                }
            }
            $manager->flush();
        }
    }
}
