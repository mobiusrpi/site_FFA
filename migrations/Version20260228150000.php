<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260228150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix MariaDB syntax for results_validated and in_progress';
    }

    public function up(Schema $schema): void
    {
        // Corrige la colonne results_validated
        $this->addSql('ALTER TABLE tests CHANGE results_validated results_validated TINYINT(1) NOT NULL DEFAULT 0');

        // Corrige la colonne in_progress
        $this->addSql('ALTER TABLE tests CHANGE in_progress in_progress TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // rollback possible si nécessaire
        $this->addSql('ALTER TABLE tests CHANGE results_validated results_validated TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE tests CHANGE in_progress in_progress TINYINT(1) DEFAULT NULL');
    }
}