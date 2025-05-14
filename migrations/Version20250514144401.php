<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514144401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE accommodations (id INT AUTO_INCREMENT NOT NULL, room VARCHAR(128) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, available TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE competition_accommodation (id INT AUTO_INCREMENT NOT NULL, competition_id INT DEFAULT NULL, accommodation_id INT DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, available TINYINT(1) DEFAULT NULL, INDEX IDX_DAED96B27B39D312 (competition_id), INDEX IDX_DAED96B28F3692CD (accommodation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation ADD CONSTRAINT FK_DAED96B27B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation ADD CONSTRAINT FK_DAED96B28F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodations (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE accommodation
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE accommodation (id INT AUTO_INCREMENT NOT NULL, room VARCHAR(128) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, price DOUBLE PRECISION DEFAULT NULL, available TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation DROP FOREIGN KEY FK_DAED96B27B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation DROP FOREIGN KEY FK_DAED96B28F3692CD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE accommodations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competition_accommodation
        SQL);
    }
}
