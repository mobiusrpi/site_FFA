<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615125331 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE type_competition ADD championship_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_competition ADD CONSTRAINT FK_FE13FB2094DDBCE9 FOREIGN KEY (championship_id) REFERENCES competitions (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_FE13FB2094DDBCE9 ON type_competition (championship_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE type_competition DROP FOREIGN KEY FK_FE13FB2094DDBCE9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_FE13FB2094DDBCE9 ON type_competition
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_competition DROP championship_id
        SQL);
    }
}
