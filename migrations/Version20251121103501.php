<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121103501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add role-based conversation system, remove GroupMessage entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE group_message DROP CONSTRAINT FK_30BD6473F675F31B');
        $this->addSql('DROP TABLE group_message');
        $this->addSql('CREATE TABLE conversation (id SERIAL NOT NULL, is_group BOOLEAN DEFAULT false NOT NULL, group_name VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conversation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE conversation_participant (id SERIAL NOT NULL, conversation_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(20) DEFAULT \'member\' NOT NULL, joined_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, left_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_398016619AC0396 ON conversation_participant (conversation_id)');
        $this->addSql('CREATE INDEX IDX_39801661A76ED395 ON conversation_participant (user_id)');
        $this->addSql('COMMENT ON COLUMN conversation_participant.joined_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participant.left_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_398016619AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_39801661A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_398016619AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_39801661A76ED395');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE conversation_participant');
    }
}
