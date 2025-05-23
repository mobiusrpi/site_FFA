<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522045452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competitions_users (competitions_id INT NOT NULL, users_id INT NOT NULL, INDEX IDX_F9E5DA4E14B3F5BE (competitions_id), INDEX IDX_F9E5DA4E67B3B43D (users_id), PRIMARY KEY(competitions_id, users_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E14B3F5BE FOREIGN KEY (competitions_id) REFERENCES competitions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD validation_payment TINYINT(1) DEFAULT NULL, DROP payment
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E14B3F5BE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE crews ADD payment LONGTEXT DEFAULT NULL, DROP validation_payment
        SQL);
    }
}
