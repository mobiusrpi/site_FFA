<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260714141543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE competition_documents (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, title VARCHAR(100) NOT NULL, filename VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', public TINYINT(1) NOT NULL, INDEX IDX_9503EEA7B39D312 (competition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE competition_documents ADD CONSTRAINT FK_9503EEA7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)');
        $this->addSql('ALTER TABLE users CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competition_documents DROP FOREIGN KEY FK_9503EEA7B39D312');
        $this->addSql('DROP TABLE competition_documents');
        $this->addSql('ALTER TABLE users CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
