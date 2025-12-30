<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251229182546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add likes_count to likable entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD likes_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE post ADD likes_count INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE post p SET likes_count = (SELECT COUNT(*) FROM "like" l WHERE l.entity_id = p.id AND l.entity_class = \'post\')');
        $this->addSql('UPDATE comment c SET likes_count = (SELECT COUNT(*) FROM "like" l WHERE l.entity_id = c.id AND l.entity_class = \'comment\')');

        $this->addSql('ALTER TABLE "like" ALTER COLUMN entity_class TYPE VARCHAR(255)');
        $this->addSql("UPDATE \"like\" SET entity_class = 'App\\Entity\\Post' WHERE entity_class = 'post'");
        $this->addSql("UPDATE \"like\" SET entity_class = 'App\\Entity\\Comment' WHERE entity_class = 'comment'");
        $this->addSql("UPDATE \"like\" SET entity_class = 'App\\Entity\\Message' WHERE entity_class = 'message'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP likes_count');
        $this->addSql('ALTER TABLE post DROP likes_count');
    }
}
