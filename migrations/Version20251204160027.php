<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204160027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unread_count column to conversation_participant table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation_participant ADD unread_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation_participant DROP unread_count');
    }
}
