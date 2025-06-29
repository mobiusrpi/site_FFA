<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250627085526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_results DROP FOREIGN KEY FK_43E230DC5FE259F6');
    $this->addSql('ALTER TABLE test_results CHANGE crew_id crew_id INT NOT NULL');
    $this->addSql('ALTER TABLE test_results ADD CONSTRAINT FK_43E230DC5FE259F6 FOREIGN KEY (crew_id) REFERENCES crews(id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results CHANGE crew_id crew_id INT DEFAULT NULL
        SQL);
    }
}
