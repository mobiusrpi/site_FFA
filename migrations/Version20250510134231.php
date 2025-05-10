<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510134231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE acommodation (id INT AUTO_INCREMENT NOT NULL, room VARCHAR(128) DEFAULT NULL, check_in DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', check_out DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', sharing TINYINT(1) DEFAULT NULL, person_sharing VARCHAR(30) DEFAULT NULL, price DOUBLE PRECISION DEFAULT NULL, availlable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD registered_by LONGTEXT NOT NULL, ADD registered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE acommodation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP registered_by, DROP registered_at
        SQL);
    }
}
