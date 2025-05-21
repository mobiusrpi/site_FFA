<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521080853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE crews_competition_accommodation (crews_id INT NOT NULL, competition_accommodation_id INT NOT NULL, INDEX IDX_70C513E0B3F00855 (crews_id), INDEX IDX_70C513E0593B5CCD (competition_accommodation_id), PRIMARY KEY(crews_id, competition_accommodation_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews_competition_accommodation ADD CONSTRAINT FK_70C513E0B3F00855 FOREIGN KEY (crews_id) REFERENCES crews (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews_competition_accommodation ADD CONSTRAINT FK_70C513E0593B5CCD FOREIGN KEY (competition_accommodation_id) REFERENCES competition_accommodation (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE crews_competition_accommodation DROP FOREIGN KEY FK_70C513E0B3F00855
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews_competition_accommodation DROP FOREIGN KEY FK_70C513E0593B5CCD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE crews_competition_accommodation
        SQL);
    }
}
