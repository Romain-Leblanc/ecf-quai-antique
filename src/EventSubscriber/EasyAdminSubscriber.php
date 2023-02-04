<?php

namespace App\EventSubscriber;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $passwordHasher)
    {}

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['addUtilisateur'],
            BeforeEntityUpdatedEvent::class => ['updateUtilisateur'], //surtout utile lors d'un reset de mot passe plutôt qu'un réel update, car l'update va de nouveau encrypter le mot de passe DEJA encrypté ...
        ];
    }

    public function updateUtilisateur(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Utilisateur)) {
            return;
        }
        $this->setValues($entity);
    }

    public function addUtilisateur(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Utilisateur)) {
            return;
        }
        $this->setValues($entity);
    }
    
    /* Encodage du mot de passe */
    public function setValues(Utilisateur $entity): void
    {
        $password = $entity->getPassword();

        // Définit le rôle, le nombre de convives par défaut (requis) et encode son mot de passe
        $entity->setRoles(['ROLE_ADMIN']);
        $entity->setNombreConvives(1);
        $entity->setPassword(
            $this->passwordHasher->hashPassword(
                $entity,
                $password
            )
        );
        // Enregistrement des données
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}