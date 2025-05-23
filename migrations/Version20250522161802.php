<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522161802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4E7B39D312
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users DROP FOREIGN KEY FK_F9E5DA4EA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE competitions_users
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions ADD role LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competitions_users (competition_id INT NOT NULL, user_id INT NOT NULL, titled VARCHAR(30) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, INDEX IDX_F9E5DA4E7B39D312 (competition_id), INDEX IDX_F9E5DA4EA76ED395 (user_id), PRIMARY KEY(competition_id, user_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4E7B39D312 FOREIGN KEY (competition_id) REFERENCES competitions (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions_users ADD CONSTRAINT FK_F9E5DA4EA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE competitions DROP role
        SQL);
    }
}
