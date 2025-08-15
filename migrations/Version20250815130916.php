<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815130916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_tokens DROP FOREIGN KEY `FK_9BACE7E1A76ED395`');
        $this->addSql('DROP INDEX UNIQ_9BACE7E1A76ED395 ON refresh_tokens');
        $this->addSql('ALTER TABLE refresh_tokens DROP user_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_tokens ADD user_id VARCHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE refresh_tokens ADD CONSTRAINT `FK_9BACE7E1A76ED395` FOREIGN KEY (user_id) REFERENCES user_entity (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1A76ED395 ON refresh_tokens (user_id)');
    }
}
