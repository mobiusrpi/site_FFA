<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250621090431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results DROP ranking, DROP gender, DROP flyingclub, DROP committee, CHANGE navigation navigation INT DEFAULT NULL, CHANGE observation observation INT DEFAULT NULL, CHANGE landing landing INT DEFAULT NULL, CHANGE flight_planning flight_planning INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results ADD ranking INT NOT NULL, ADD gender VARCHAR(10) DEFAULT NULL, ADD flyingclub VARCHAR(128) DEFAULT NULL, ADD committee VARCHAR(50) DEFAULT NULL, CHANGE navigation navigation INT NOT NULL, CHANGE observation observation INT NOT NULL, CHANGE landing landing INT NOT NULL, CHANGE flight_planning flight_planning INT NOT NULL
        SQL);
    }
}
