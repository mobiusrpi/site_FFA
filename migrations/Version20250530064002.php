<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250530064002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE results (id INT AUTO_INCREMENT NOT NULL, ranking INT NOT NULL, penalties INT NOT NULL, crew VARCHAR(255) NOT NULL, gender VARCHAR(10) DEFAULT NULL, flyingclub VARCHAR(50) DEFAULT NULL, committee VARCHAR(50) DEFAULT NULL, navigation INT NOT NULL, observation INT NOT NULL, landing INT NOT NULL, flight_planning INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE results
        SQL);
    }
}
