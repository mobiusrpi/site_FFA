<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522053603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E14B3F5BE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E67B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F9E5DA4E14B3F5BE ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F9E5DA4E67B3B43D ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD id INT AUTO_INCREMENT NOT NULL, ADD competition_id INT DEFAULT NULL, ADD crew_id INT DEFAULT NULL, ADD tilted VARCHAR(30) DEFAULT NULL, DROP competitions_id, DROP users_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E5FE259F6 FOREIGN KEY (crew_id) REFERENCES crews (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9E5DA4E7B39D312 ON competitions_users (competition_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9E5DA4E5FE259F6 ON competitions_users (crew_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E7B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E5FE259F6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F9E5DA4E7B39D312 ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_F9E5DA4E5FE259F6 ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD competitions_id INT NOT NULL, ADD users_id INT NOT NULL, DROP id, DROP competition_id, DROP crew_id, DROP tilted
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E14B3F5BE FOREIGN KEY (competitions_id) REFERENCES competitions (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9E5DA4E14B3F5BE ON competitions_users (competitions_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F9E5DA4E67B3B43D ON competitions_users (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD PRIMARY KEY (competitions_id, users_id)
        SQL);
    }
}
