<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250826145600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update user table to add profile_picture, bio, and is_confirmed fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD profile_picture VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD bio VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD is_confirmed BOOLEAN DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP profile_picture');
        $this->addSql('ALTER TABLE "user" DROP bio');
        $this->addSql('ALTER TABLE "user" DROP is_confirmed');
    }
}
