<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522171545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_comp_user ON competitions_users (competition_id, user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX uniq_comp_user ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD PRIMARY KEY (competition_id, user_id)
        SQL);
    }
}
