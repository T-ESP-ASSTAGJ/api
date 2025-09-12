<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250825144559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Post Table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE post (id SERIAL NOT NULL, user_id INT NOT NULL, song_preview_url VARCHAR(500) DEFAULT NULL, caption TEXT DEFAULT NULL, track JSON NOT NULL, photo_url VARCHAR(500) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN post.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN post.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE post');
    }
}
