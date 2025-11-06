<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251106151546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE conversation (id SERIAL NOT NULL, type VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conversation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE conversation_participant (conversation_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(conversation_id, user_id))');
        $this->addSql('CREATE INDEX IDX_398016619AC0396 ON conversation_participant (conversation_id)');
        $this->addSql('CREATE INDEX IDX_39801661A76ED395 ON conversation_participant (user_id)');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_398016619AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6BD307F9AC0396 ON message (conversation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_398016619AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_39801661A76ED395');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_participant');
        $this->addSql('DROP INDEX IDX_B6BD307F9AC0396');
    }
}
