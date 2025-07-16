<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715164755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE test_start_order (id INT AUTO_INCREMENT NOT NULL, crew_id INT NOT NULL, test_id INT NOT NULL, start_order INT DEFAULT NULL, take_off_time DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_DF0232D75FE259F6 (crew_id), INDEX IDX_DF0232D71E5D0459 (test_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_start_order ADD CONSTRAINT FK_DF0232D75FE259F6 FOREIGN KEY (crew_id) REFERENCES crews (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_start_order ADD CONSTRAINT FK_DF0232D71E5D0459 FOREIGN KEY (test_id) REFERENCES tests (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE test_start_order DROP FOREIGN KEY FK_DF0232D75FE259F6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE test_start_order DROP FOREIGN KEY FK_DF0232D71E5D0459
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE test_start_order
        SQL);
    }
}
