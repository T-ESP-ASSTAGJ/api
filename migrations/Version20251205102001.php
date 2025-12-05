<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205102001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track_source creation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE track_source (id SERIAL NOT NULL, track_id INT NOT NULL, platform VARCHAR(50) NOT NULL, platform_track_id VARCHAR(255) NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1748BE9A5ED23C43 ON track_source (track_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_track_platform ON track_source (track_id, platform, platform_track_id)');
        $this->addSql('COMMENT ON COLUMN track_source.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN track_source.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE track_source ADD CONSTRAINT FK_1748BE9A5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track_source DROP CONSTRAINT FK_1748BE9A5ED23C43');
        $this->addSql('DROP TABLE track_source');
    }
}
