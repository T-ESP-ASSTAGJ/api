<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251114104042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding artist_source table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE artist_source (id SERIAL NOT NULL, artist_id INT NOT NULL, platform VARCHAR(50) NOT NULL, platform_artist_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD88730DB7970CF8 ON artist_source (artist_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_artist_platform ON artist_source (artist_id, platform, platform_artist_id)');
        $this->addSql('COMMENT ON COLUMN artist_source.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN artist_source.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE artist_source ADD CONSTRAINT FK_AD88730DB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE artist DROP metadata');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist_source DROP CONSTRAINT FK_AD88730DB7970CF8');
        $this->addSql('DROP TABLE artist_source');
        $this->addSql('ALTER TABLE artist ADD metadata JSON NOT NULL');
    }
}
