<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251106100750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change of Post, and creation of Track';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE track (id SERIAL NOT NULL, artist_id INT NOT NULL, title VARCHAR(255) NOT NULL, cover_url TEXT DEFAULT NULL, metadata JSON NOT NULL, length INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D6E3F8A6B7970CF8 ON track (artist_id)');
        $this->addSql('COMMENT ON COLUMN track.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN track.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE track ADD CONSTRAINT FK_D6E3F8A6B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD track_id INT NOT NULL');
        $this->addSql('ALTER TABLE post DROP track');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D5ED23C43 ON post (track_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D5ED23C43');
        $this->addSql('ALTER TABLE track DROP CONSTRAINT FK_D6E3F8A6B7970CF8');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP INDEX IDX_5A8A6C8D5ED23C43');
        $this->addSql('ALTER TABLE post ADD track JSON NOT NULL');
        $this->addSql('ALTER TABLE post DROP track_id');
    }
}
