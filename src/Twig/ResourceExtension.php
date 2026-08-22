<?php

namespace App\Twig;

use App\Entity\Resources;
use App\Repository\ResourcesRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ResourceExtension extends AbstractExtension
{
    public function __construct(
        private ResourcesRepository $resourcesRepository
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'resources_by_category',
                [$this, 'getResourcesByCategory']
            ),
        ];
    }

    public function getResourcesByCategory(
        string $category
    ): array {
        return $this->resourcesRepository
            ->findEnabledByCategory($category);
    }
}