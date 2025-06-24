<?php

namespace App\EventListener;

use App\Entity\Tests;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class TestCodeGeneratorListener
{
   public function prePersist(object $entity, LifecycleEventArgs $args): void
    {
        if (!$entity instanceof Tests) {
            return;
        }

        $entityManager = $args->getObjectManager();

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \LogicException('Expected Doctrine ORM EntityManager.');
        }

        if (empty($entity->getName())) {
            // Detach entity to prevent persisting it if name is empty
            $entityManager->detach($entity);
        }
            // Now generate a unique code based on the name
        if ($entity->getCode() !== null) {
            return; // code already set
        }
        
      
        $nameOrDefault = $entity->getName();
        $typeLabel = strtoupper(str_replace(' ', '', $nameOrDefault));

        do {
            $randomSuffix = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);
            $code = sprintf('%s-%s', $typeLabel, $randomSuffix);

            $existingTest = $entityManager->getRepository(Tests::class)->findOneBy(['code' => $code]);
        } while ($existingTest !== null);

        $entity->setCode($code);
    }
}