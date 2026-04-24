<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424123049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialize user_parameter for existing users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO user_parameter (user_id, is_followers_public, is_following_public, is_stats_public, is_playlist_public, is_likes_public, notif_new_follower, notif_new_like, notif_new_comment, notif_new_message, created_at, updated_at) 
            SELECT id, true, true, true, true, true, true, true, true, true, NOW(), NOW() 
            FROM "user" 
            WHERE id NOT IN (SELECT user_id FROM user_parameter)');
    }

    public function down(Schema $schema): void
    {
    }
}
