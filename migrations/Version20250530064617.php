<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250530064617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD competition_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results ADD CONSTRAINT FK_9FA3E4147B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9FA3E4147B39D312 ON results (competition_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP FOREIGN KEY FK_9FA3E4147B39D312
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9FA3E4147B39D312 ON results
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE results DROP competition_id
        SQL);
    }
}
