<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516165904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Cascade Delete ;Replace boolean visibility fields with VisibilityEnum (public/friends/private) on user_parameter';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_39801661A76ED395');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT FK_68344470AC24F853');
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT FK_68344470AF2612FD');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AF2612FD FOREIGN KEY (followed_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B3A76ED395');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B3A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784A76ED395');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE token DROP CONSTRAINT FK_5F37A13BA76ED395');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_parameter ADD followers_visibility VARCHAR(255) DEFAULT \'public\' NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD following_visibility VARCHAR(255) DEFAULT \'public\' NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD stats_visibility VARCHAR(255) DEFAULT \'public\' NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD playlist_visibility VARCHAR(255) DEFAULT \'public\' NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD likes_visibility VARCHAR(255) DEFAULT \'public\' NOT NULL');
        $this->addSql('ALTER TABLE user_parameter DROP is_followers_public');
        $this->addSql('ALTER TABLE user_parameter DROP is_following_public');
        $this->addSql('ALTER TABLE user_parameter DROP is_stats_public');
        $this->addSql('ALTER TABLE user_parameter DROP is_playlist_public');
        $this->addSql('ALTER TABLE user_parameter DROP is_likes_public');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT fk_ac6340b3a76ed395');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT fk_ac6340b3a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT fk_68344470ac24f853');
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT fk_68344470af2612fd');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT fk_68344470ac24f853 FOREIGN KEY (follower_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT fk_68344470af2612fd FOREIGN KEY (followed_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT fk_39801661a76ed395');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT fk_39801661a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8da76ed395');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8da76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE token DROP CONSTRAINT fk_5f37a13ba76ed395');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT fk_5f37a13ba76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT fk_b6bd307ff675f31b');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT fk_b6bd307ff675f31b FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT fk_9474526ca76ed395');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT fk_9474526ca76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_parameter ADD is_followers_public BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD is_following_public BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD is_stats_public BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD is_playlist_public BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE user_parameter ADD is_likes_public BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE user_parameter DROP followers_visibility');
        $this->addSql('ALTER TABLE user_parameter DROP following_visibility');
        $this->addSql('ALTER TABLE user_parameter DROP stats_visibility');
        $this->addSql('ALTER TABLE user_parameter DROP playlist_visibility');
        $this->addSql('ALTER TABLE user_parameter DROP likes_visibility');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT fk_c42f7784a76ed395');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT fk_c42f7784a76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
