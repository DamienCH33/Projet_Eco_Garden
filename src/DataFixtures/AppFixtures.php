<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $city = new City();
        $city->setName('Bordeaux');
        $city->setPostalCode('33100');
        $city->setCountry('FR');

        $manager->persist($city);

        $admin = new User();
        $admin->setEmail('admin@test.fr');
        $admin->setPostalCode('33100');
        $admin->setCity($city);
        $admin->setRoles(['ROLE_ADMIN']);

        $admin->setPassword(
            $this->hasher->hashPassword($admin, 'password')
        );

        $manager->persist($admin);

        $manager->flush();
    }
}
