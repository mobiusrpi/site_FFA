<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309164127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_results ADD ranking INT NOT NULL, ADD gender VARCHAR(10) DEFAULT NULL, ADD flyingclub VARCHAR(128) DEFAULT NULL, ADD committee VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE license_ffa license_ffa VARCHAR(9) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(100) DEFAULT NULL, CHANGE lastname lastname VARCHAR(30) NOT NULL, CHANGE firstname firstname VARCHAR(30) NOT NULL, CHANGE flyingclub flyingclub VARCHAR(100) DEFAULT NULL, CHANGE phone phone VARCHAR(30) DEFAULT NULL, CHANGE gender gender VARCHAR(255) DEFAULT NULL, CHANGE committee committee VARCHAR(255) DEFAULT NULL, CHANGE polo_size polo_size VARCHAR(255) DEFAULT NULL, CHANGE api_token api_token VARCHAR(100) DEFAULT NULL, CHANGE id_club id_club VARCHAR(10) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE test_results DROP ranking, DROP gender, DROP flyingclub, DROP committee');
        $this->addSql('ALTER TABLE users CHANGE email email VARCHAR(180) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE password password VARCHAR(255) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE lastname lastname VARCHAR(30) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE firstname firstname VARCHAR(30) CHARACTER SET utf8mb3 NOT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE reset_token reset_token VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE license_ffa license_ffa VARCHAR(9) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE flyingclub flyingclub VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE id_club id_club VARCHAR(10) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE phone phone VARCHAR(30) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE gender gender VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE committee committee VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE polo_size polo_size VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, CHANGE api_token api_token VARCHAR(100) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`');
    }
}
