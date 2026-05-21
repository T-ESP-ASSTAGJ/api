<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260521130925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Upgrade profile_picture size';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER profile_picture TYPE VARCHAR(500)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER profile_picture TYPE VARCHAR(255)');
    }
}
