<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204145510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change of Post & Track Output for post';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP song_preview_url');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE track DROP length');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE track ADD length INT NOT NULL');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post ADD song_preview_url VARCHAR(500) DEFAULT NULL');
    }
}
