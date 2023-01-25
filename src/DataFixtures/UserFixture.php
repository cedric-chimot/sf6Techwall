<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

//'implements FixtureGroupInterface' : pour qu'au lancement des fixtures seul le groupe 'user' soit charger
class UserFixture extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}
    public function load(ObjectManager $manager): void
    {
        //création du mail et du MDP
        $admin1 = new User();
        $admin1->setEmail('admin@gmail.com');
        //hachage du MDP grace à la fonctionnalité de sécurité
        $admin1->setPassword($this->hasher->hashPassword($admin1,'admin'));
        //attribution du rôle
        $admin1->setRoles(['ROLE_ADMIN']);

        $admin2 = new User();
        $admin2->setEmail('admin2@gmail.com');
        $admin2->setPassword($this->hasher->hashPassword($admin2, 'admin'));
        $admin2->setRoles(['ROLE_ADMIN']);

        //on fait persister les nouveaux admins
        $manager->persist($admin1);
        $manager->persist($admin2);

        //boucle for pour créer de nouveaux users
        //pas besoin de rôle, celui-ci étant défini 'user' par défaut
        for ($i=1; $i<=5;$i++) {
            $user = new User();
            $user->setEmail("user$i@gmail.com");
            $user->setPassword($this->hasher->hashPassword($user,'user'));
            $manager->persist($user);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user'];
    }
}       