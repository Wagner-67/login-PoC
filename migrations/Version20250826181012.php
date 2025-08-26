<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826181012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset_token CHANGE resetesh_tokens reset_tokens VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE two_factor_auth ADD twofactorauth_token VARCHAR(10) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2040D91C417CD9B7 ON two_factor_auth (twofactorauth_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE password_reset_token CHANGE reset_tokens resetesh_tokens VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_2040D91C417CD9B7 ON two_factor_auth');
        $this->addSql('ALTER TABLE two_factor_auth DROP twofactorauth_token');
    }
}
