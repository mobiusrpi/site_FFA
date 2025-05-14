<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250514083320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competitions_accommodation (competitions_id INT NOT NULL, accommodation_id INT NOT NULL, INDEX IDX_9979F56C14B3F5BE (competitions_id), INDEX IDX_9979F56C8F3692CD (accommodation_id), PRIMARY KEY(competitions_id, accommodation_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_accommodation ADD CONSTRAINT FK_9979F56C14B3F5BE FOREIGN KEY (competitions_id) REFERENCES competitions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_accommodation ADD CONSTRAINT FK_9979F56C8F3692CD FOREIGN KEY (accommodation_id) REFERENCES accommodation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD information LONGTEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_accommodation DROP FOREIGN KEY FK_9979F56C14B3F5BE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_accommodation DROP FOREIGN KEY FK_9979F56C8F3692CD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitions_accommodation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions DROP information
        SQL);
    }
}
