<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624070652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE aircrafts ADD user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE aircrafts ADD CONSTRAINT FK_59AF8E00A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_59AF8E00A76ED395 ON aircrafts (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE aircrafts DROP FOREIGN KEY FK_59AF8E00A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_59AF8E00A76ED395 ON aircrafts
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE aircrafts DROP user_id
        SQL);
    }
}
