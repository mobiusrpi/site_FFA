<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix MariaDB syntax: create results_validated and ensure in_progress NOT NULL DEFAULT 0';
    }

    public function up(Schema $schema): void
    {
        // Crée results_validated si elle n'existe pas encore
        $this->addSql('ALTER TABLE tests ADD COLUMN results_validated TINYINT(1) NOT NULL DEFAULT 0');

        // S'assure que in_progress est correct
        $this->addSql('ALTER TABLE tests CHANGE in_progress in_progress TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // rollback si nécessaire
        $this->addSql('ALTER TABLE tests DROP COLUMN results_validated');
        $this->addSql('ALTER TABLE tests CHANGE in_progress in_progress TINYINT(1) DEFAULT NULL');
    }
}