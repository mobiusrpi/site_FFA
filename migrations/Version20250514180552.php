<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514180552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE accommodations DROP check_in, DROP check_out, DROP sharing, DROP person_sharing
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE accommodations ADD check_in DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD check_out DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', ADD sharing TINYINT(1) DEFAULT NULL, ADD person_sharing VARCHAR(30) DEFAULT NULL
        SQL);
    }
}
