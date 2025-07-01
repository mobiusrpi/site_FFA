<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701071708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EB7B39D312;
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews CHANGE competition_id competition_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions(id);
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EB7B39D312;
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews CHANGE competition_id competition_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions(id);
        SQL);
    }
}
