<?php 

namespace App\EventSubscriber;

use App\Entity\Users;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordSubscriber implements EventSubscriber
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->encodePassword($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->encodePassword($args->getObject());
    }

    private function encodePassword(object $entity): void
    {
        if (!$entity instanceof Users) {
            return;
        }

        if ($entity->getPlainPassword()) {
            $entity->setPassword(
                $this->passwordHasher->hashPassword($entity, $entity->getPlainPassword())
            );
        }
    }
}

