<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260516173512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace boolean notification fields with VisibilityEnum (public/friends/private) on user_parameter';
    }

    public function up(Schema $schema): void
    {
        $usingTrue = "CASE WHEN %s THEN 'public' ELSE 'private' END";
        foreach (['notif_new_follower', 'notif_new_like', 'notif_new_comment', 'notif_new_message'] as $col) {
            $this->addSql(\sprintf(
                'ALTER TABLE user_parameter ALTER %s TYPE VARCHAR(255) USING %s',
                $col,
                \sprintf($usingTrue, $col),
            ));
            $this->addSql(\sprintf("ALTER TABLE user_parameter ALTER %s SET DEFAULT 'public'", $col));
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['notif_new_follower', 'notif_new_like', 'notif_new_comment', 'notif_new_message'] as $col) {
            $this->addSql(\sprintf(
                "ALTER TABLE user_parameter ALTER %s TYPE BOOLEAN USING CASE WHEN %s = 'public' THEN TRUE ELSE FALSE END",
                $col,
                $col,
            ));
            $this->addSql(\sprintf('ALTER TABLE user_parameter ALTER %s SET DEFAULT true', $col));
        }
    }
}
