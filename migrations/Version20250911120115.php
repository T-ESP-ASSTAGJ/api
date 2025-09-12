<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250911120115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Modify User is_confirmed to is_verified and add needs_profile field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD needs_profile BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN is_confirmed TO is_verified');
        $this->addSql('ALTER TABLE "user" ALTER username DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_number DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER username SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_number SET NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP needs_profile');
        $this->addSql('ALTER TABLE "user" RENAME COLUMN is_verified TO is_confirmed');
    }
}
