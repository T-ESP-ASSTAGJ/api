<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250902143930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table token for multi-platform support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE token (id SERIAL NOT NULL, user_id INT NOT NULL, platform VARCHAR(50) NOT NULL, access_token TEXT NOT NULL, refresh_token TEXT DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, platform_user_id VARCHAR(255) NOT NULL, scopes JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F37A13BA76ED395 ON token (user_id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13BA76ED395');
        $this->addSql('DROP TABLE token');
    }
}
