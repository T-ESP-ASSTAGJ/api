<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260205135012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add views_count to Posts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD views_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP views_count');
    }
}
