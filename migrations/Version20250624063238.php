<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624063238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE aircrafts (id INT AUTO_INCREMENT NOT NULL, callsign VARCHAR(8) NOT NULL, speed VARCHAR(255) NOT NULL, flyingclub VARCHAR(30) DEFAULT NULL, type VARCHAR(20) DEFAULT NULL, oaci VARCHAR(8) DEFAULT NULL, brand VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE navigations (id INT AUTO_INCREMENT NOT NULL, nav_id INT DEFAULT NULL, INDEX IDX_AD21D8F3F03A7216 (nav_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE navigations ADD CONSTRAINT FK_AD21D8F3F03A7216 FOREIGN KEY (nav_id) REFERENCES competitions (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE navigations DROP FOREIGN KEY FK_AD21D8F3F03A7216
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE aircrafts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE navigations
        SQL);
    }
}
