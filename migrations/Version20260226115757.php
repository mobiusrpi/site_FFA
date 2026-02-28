<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260226115757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Placeholder to satisfy Doctrine for executed unavailable migration';
    }

    public function up(Schema $schema): void
    {
        // rien à faire, déjà appliquée
    }

    public function down(Schema $schema): void
    {
        // rien à faire
    }
}