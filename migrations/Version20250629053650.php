<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250629053650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE navigations DROP FOREIGN KEY FK_AD21D8F3F03A7216
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE navigations
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE navigations (id INT AUTO_INCREMENT NOT NULL, nav_id INT DEFAULT NULL, INDEX IDX_AD21D8F3F03A7216 (nav_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE navigations ADD CONSTRAINT FK_AD21D8F3F03A7216 FOREIGN KEY (nav_id) REFERENCES competitions (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
