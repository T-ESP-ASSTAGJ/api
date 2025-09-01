<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826150510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE spotify_token (id SERIAL NOT NULL, user_id INT NOT NULL, access_token TEXT NOT NULL, refresh_token TEXT DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, spotify_user_id VARCHAR(255) NOT NULL, scopes JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EBF7771FA76ED395 ON spotify_token (user_id)');
        $this->addSql('COMMENT ON COLUMN spotify_token.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN spotify_token.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE spotify_token ADD CONSTRAINT FK_EBF7771FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE spotify_token DROP CONSTRAINT FK_EBF7771FA76ED395');
        $this->addSql('DROP TABLE spotify_token');
    }
}
