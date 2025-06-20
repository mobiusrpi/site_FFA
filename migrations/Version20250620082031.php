<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620082031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE test_results (id INT AUTO_INCREMENT NOT NULL, test_id INT NOT NULL, crew_id INT NOT NULL, ranking INT NOT NULL, gender VARCHAR(10) DEFAULT NULL, flyingclub VARCHAR(128) DEFAULT NULL, committee VARCHAR(50) DEFAULT NULL, navigation INT NOT NULL, observation INT NOT NULL, landing INT NOT NULL, flight_planning INT NOT NULL, category VARCHAR(15) DEFAULT NULL, archived_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', literal_crew VARCHAR(255) DEFAULT NULL, status VARCHAR(20) DEFAULT NULL, INDEX IDX_43E230DC1E5D0459 (test_id), INDEX IDX_43E230DC5FE259F6 (crew_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tests (id INT AUTO_INCREMENT NOT NULL, competition_id INT NOT NULL, type VARCHAR(20) NOT NULL, scheduled_at DATETIME NOT NULL, INDEX IDX_1260FC5E7B39D312 (competition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results ADD CONSTRAINT FK_43E230DC1E5D0459 FOREIGN KEY (test_id) REFERENCES tests (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results ADD CONSTRAINT FK_43E230DC5FE259F6 FOREIGN KEY (crew_id) REFERENCES crews (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tests ADD CONSTRAINT FK_1260FC5E7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results DROP FOREIGN KEY FK_43E230DC1E5D0459
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_results DROP FOREIGN KEY FK_43E230DC5FE259F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tests DROP FOREIGN KEY FK_1260FC5E7B39D312
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE test_results
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tests
        SQL);
    }
}
