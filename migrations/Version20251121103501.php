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
        $this->addSql('ALTER TABLE conversation ADD is_group BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE conversation ADD group_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation DROP type');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT FK_398016619AC0396');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT conversation_participant_pkey');
        $this->addSql('ALTER TABLE conversation_participant ADD id SERIAL NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant ADD joined_at TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE conversation_participant ADD left_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN conversation_participant.joined_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participant.left_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT FK_398016619AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conversation_participant DROP CONSTRAINT fk_398016619ac0396');
        $this->addSql('DROP INDEX conversation_participant_pkey');
        $this->addSql('ALTER TABLE conversation_participant DROP id');
        $this->addSql('ALTER TABLE conversation_participant DROP joined_at');
        $this->addSql('ALTER TABLE conversation_participant DROP left_at');
        $this->addSql('ALTER TABLE conversation_participant ADD CONSTRAINT fk_398016619ac0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation_participant ADD PRIMARY KEY (conversation_id, user_id)');
        $this->addSql('ALTER TABLE conversation ADD type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE conversation DROP is_group');
        $this->addSql('ALTER TABLE conversation DROP group_name');
    }
}
