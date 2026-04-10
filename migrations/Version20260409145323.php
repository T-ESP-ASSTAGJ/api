<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409145323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Message: remove is_read, read_at; Conversation: add last_read_at, remove unread_count';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP is_read');
        $this->addSql('ALTER TABLE conversation_participant ADD last_read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation_participant DROP unread_count');
        $this->addSql('COMMENT ON COLUMN conversation_participant.last_read_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message DROP read_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD is_read BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant ADD unread_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant DROP last_read_at');
        $this->addSql('ALTER TABLE message ADD read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN message.read_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
