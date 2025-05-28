<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528062923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competition_accommodation (id INT AUTO_INCREMENT NOT NULL, competition_id INT DEFAULT NULL, accommodation_id INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_DAED96B27B39D312 (competition_id), INDEX IDX_DAED96B28F3692CD (accommodation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE crew_competition_accommodation (competition_accommodation_id INT NOT NULL, crews_id INT NOT NULL, INDEX IDX_43238728593B5CCD (competition_accommodation_id), INDEX IDX_43238728B3F00855 (crews_id), PRIMARY KEY(competition_accommodation_id, crews_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE competitions_users (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, user_id INT NOT NULL, role JSON NOT NULL, INDEX IDX_F9E5DA4E7B39D312 (competition_id), INDEX IDX_F9E5DA4EA76ED395 (user_id), UNIQUE INDEX uniq_comp_user (competition_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE crews (id INT AUTO_INCREMENT NOT NULL, competition_id INT DEFAULT NULL, pilot_id INT DEFAULT NULL, navigator_id INT DEFAULT NULL, registeredby_id INT NOT NULL, category VARCHAR(255) DEFAULT NULL, callsign VARCHAR(8) DEFAULT NULL, aircraft_speed VARCHAR(255) DEFAULT NULL, aircraft_type VARCHAR(20) DEFAULT NULL, aircraft_flyingclub VARCHAR(30) DEFAULT NULL, aircraft_sharing TINYINT(1) DEFAULT NULL, pilot_shared VARCHAR(30) DEFAULT NULL, validation_payment TINYINT(1) DEFAULT NULL, registered_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_3EE854EB7B39D312 (competition_id), INDEX IDX_3EE854EBCE55439B (pilot_id), INDEX IDX_3EE854EB473C72C (navigator_id), INDEX IDX_3EE854EBF1C1B900 (registeredby_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation ADD CONSTRAINT FK_DAED96B27B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation ADD CONSTRAINT FK_DAED96B28F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crew_competition_accommodation ADD CONSTRAINT FK_43238728593B5CCD FOREIGN KEY (competition_accommodation_id) REFERENCES competition_accommodation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crew_competition_accommodation ADD CONSTRAINT FK_43238728B3F00855 FOREIGN KEY (crews_id) REFERENCES crews (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EBCE55439B FOREIGN KEY (pilot_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EB473C72C FOREIGN KEY (navigator_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD CONSTRAINT FK_3EE854EBF1C1B900 FOREIGN KEY (registeredby_id) REFERENCES users (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation DROP FOREIGN KEY FK_DAED96B27B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competition_accommodation DROP FOREIGN KEY FK_DAED96B28F3692CD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crew_competition_accommodation DROP FOREIGN KEY FK_43238728593B5CCD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crew_competition_accommodation DROP FOREIGN KEY FK_43238728B3F00855
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E7B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4EA76ED395
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
            ALTER TABLE crews DROP FOREIGN KEY FK_3EE854EBF1C1B900
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competition_accommodation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE crew_competition_accommodation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE crews
        SQL);
    }
}
