<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522085443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'alter notify parameters back to bool';
    }

    public function up(Schema $schema): void
    {
        $columns = ['notif_new_follower', 'notif_new_like', 'notif_new_comment', 'notif_new_message'];
        foreach ($columns as $col) {
            $this->addSql("ALTER TABLE user_parameter ALTER $col DROP DEFAULT");
            $this->addSql("ALTER TABLE user_parameter ALTER $col TYPE BOOLEAN USING ($col != 'private')");
            $this->addSql("ALTER TABLE user_parameter ALTER $col SET DEFAULT true");
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_parameter ALTER notif_new_follower TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_parameter ALTER notif_new_like TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_parameter ALTER notif_new_comment TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE user_parameter ALTER notif_new_message TYPE VARCHAR(255)');
    }
}
