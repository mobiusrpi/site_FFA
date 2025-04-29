<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428180127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competitions (id INT AUTO_INCREMENT NOT NULL, typecompetition_id INT NOT NULL, name VARCHAR(50) NOT NULL, start_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', start_registration DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_registration DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', location VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_A7DD463DA4842409 (typecompetition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE competitors (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(30) NOT NULL, firstname VARCHAR(30) NOT NULL, ffa_licence VARCHAR(15) NOT NULL, date_birth DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', flyingclub VARCHAR(30) DEFAULT NULL, email VARCHAR(128) NOT NULL, phone VARCHAR(30) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, committee VARCHAR(255) DEFAULT NULL, polo_size VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2DED50C648C7D41B (ffa_licence), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE crews (id INT AUTO_INCREMENT NOT NULL, competition_id INT DEFAULT NULL, pilot_id INT DEFAULT NULL, navigator_id INT DEFAULT NULL, category VARCHAR(255) DEFAULT NULL, callsign VARCHAR(8) DEFAULT NULL, aircraft_speed VARCHAR(255) DEFAULT NULL, aircraft_type VARCHAR(20) DEFAULT NULL, aircraft_flyingclub VARCHAR(30) DEFAULT NULL, aircraft_sharing TINYINT(1) DEFAULT NULL, pilot_shared VARCHAR(30) DEFAULT NULL, payment LONGTEXT DEFAULT NULL, INDEX IDX_3EE854EB7B39D312 (competition_id), INDEX IDX_3EE854EBCE55439B (pilot_id), INDEX IDX_3EE854EB473C72C (navigator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_competition (id INT AUTO_INCREMENT NOT NULL, typecomp VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD CONSTRAINT FK_A7DD463DA4842409 FOREIGN KEY (typecompetition_id) REFERENCES type_competition (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EBCE55439B FOREIGN KEY (pilot_id) REFERENCES competitors (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB473C72C FOREIGN KEY (navigator_id) REFERENCES competitors (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions DROP FOREIGN KEY FK_A7DD463DA4842409
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EB7B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EBCE55439B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EB473C72C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitors
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE crews
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_competition
        SQL);
    }
}
