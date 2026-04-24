<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424115847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_parameter';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_parameter (id SERIAL NOT NULL, user_id INT NOT NULL, is_followers_public BOOLEAN DEFAULT true NOT NULL, is_following_public BOOLEAN DEFAULT true NOT NULL, is_stats_public BOOLEAN DEFAULT true NOT NULL, is_playlist_public BOOLEAN DEFAULT true NOT NULL, is_likes_public BOOLEAN DEFAULT true NOT NULL, notif_new_follower BOOLEAN DEFAULT true NOT NULL, notif_new_like BOOLEAN DEFAULT true NOT NULL, notif_new_comment BOOLEAN DEFAULT true NOT NULL, notif_new_message BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A771CF4A76ED395 ON user_parameter (user_id)');
        $this->addSql('COMMENT ON COLUMN user_parameter.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN user_parameter.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_parameter ADD CONSTRAINT FK_2A771CF4A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_parameter DROP CONSTRAINT FK_2A771CF4A76ED395');
        $this->addSql('DROP TABLE user_parameter');
    }
}
