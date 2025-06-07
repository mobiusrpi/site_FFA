<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250607091312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD crew_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E4145FE259F6 FOREIGN KEY (crew_id) REFERENCES crews (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9FA3E4145FE259F6 ON results (crew_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP FOREIGN KEY FK_9FA3E4145FE259F6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9FA3E4145FE259F6 ON results
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP crew_id
        SQL);
    }
}
