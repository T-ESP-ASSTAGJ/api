<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260103155216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alter Post : back and front image, update Track';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD back_image VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE post RENAME COLUMN photo_url TO front_image');

        // Add new columns as nullable first
        $this->addSql('ALTER TABLE track ADD song_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD artist_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD release_year INT DEFAULT NULL');

        // Populate song_id and artist_name from existing data
        // Set song_id to '1' for existing tracks without one
        // Set artist_name from the artist relationship
        $this->addSql('UPDATE track SET song_id = \'1\' WHERE song_id IS NULL');
        $this->addSql('UPDATE track SET artist_name = (SELECT name FROM artist WHERE artist.id = track.artist_id) WHERE artist_name IS NULL');

        // Now make the columns NOT NULL
        $this->addSql('ALTER TABLE track ALTER COLUMN song_id SET NOT NULL');
        $this->addSql('ALTER TABLE track ALTER COLUMN artist_name SET NOT NULL');

        // Drop old columns and constraints
        $this->addSql('ALTER TABLE track DROP CONSTRAINT fk_d6e3f8a6b7970cf8');
        $this->addSql('DROP INDEX idx_d6e3f8a6b7970cf8');
        $this->addSql('ALTER TABLE track DROP artist_id');
        $this->addSql('ALTER TABLE track DROP cover_url');
        $this->addSql('ALTER TABLE track DROP metadata');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD photo_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE post DROP front_image');
        $this->addSql('ALTER TABLE post DROP back_image');

        // Add new columns without constraints first
        $this->addSql('ALTER TABLE track ADD artist_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD cover_url TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE track ADD metadata JSON DEFAULT \'{}\'::json');

        // Create a dummy artist if none exists
        $this->addSql('INSERT INTO artist (name, created_at, updated_at) SELECT \'Unknown Artist\', NOW(), NOW() WHERE NOT EXISTS (SELECT 1 FROM artist LIMIT 1)');

        // Set artist_id to the first available artist for existing tracks
        $this->addSql('UPDATE track SET artist_id = (SELECT id FROM artist ORDER BY id LIMIT 1) WHERE artist_id IS NULL');
        $this->addSql('UPDATE track SET metadata = \'{}\'::json WHERE metadata IS NULL');

        // Make columns NOT NULL
        $this->addSql('ALTER TABLE track ALTER COLUMN artist_id SET NOT NULL');
        $this->addSql('ALTER TABLE track ALTER COLUMN metadata SET NOT NULL');

        // Drop new columns
        $this->addSql('ALTER TABLE track DROP song_id');
        $this->addSql('ALTER TABLE track DROP artist_name');
        $this->addSql('ALTER TABLE track DROP release_year');

        // Add constraint and index
        $this->addSql('ALTER TABLE track ADD CONSTRAINT fk_d6e3f8a6b7970cf8 FOREIGN KEY (artist_id) REFERENCES artist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d6e3f8a6b7970cf8 ON track (artist_id)');
    }
}
