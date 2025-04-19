<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Event;
use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@eventopia.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $manager->persist($admin);

        // Création d'un utilisateur normal
        $user = new User();
        $user->setEmail('user@eventopia.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setRoles(['ROLE_USER']);
        $user->setNom('User');
        $user->setPrenom('Normal');
        $manager->persist($user);

        // Création de quelques posts
        for ($i = 1; $i <= 5; $i++) {
            $post = new Post();
            $post->setTitre("Post $i");
            $post->setContenu("Contenu du post $i");
            $post->setDateCreation(new \DateTime());
            $post->setUser($user);
            $manager->persist($post);
        }

        // Création de quelques événements
        for ($i = 1; $i <= 3; $i++) {
            $event = new Event();
            $event->setTitre("Événement $i");
            $event->setDescription("Description de l'événement $i");
            $event->setDateDebut(new \DateTime());
            $event->setDateFin((new \DateTime())->modify('+2 days'));
            $event->setLieu("Lieu $i");
            $event->setUser($user);
            $manager->persist($event);
        }

        // Création de quelques services
        $services = ['Photographie', 'Catering', 'Animation'];
        foreach ($services as $index => $serviceName) {
            $service = new Service();
            $service->setNom($serviceName);
            $service->setDescription("Description du service $serviceName");
            $service->setPrix(100 * ($index + 1));
            $service->setUser($user);
            $manager->persist($service);
        }

        $manager->flush();
    }
}