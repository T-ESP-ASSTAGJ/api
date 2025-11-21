<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251121103501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE group_message_id_seq CASCADE');
        $this->addSql('ALTER TABLE group_message DROP CONSTRAINT fk_30bd6473f675f31b');
        $this->addSql('DROP TABLE group_message');
        $this->addSql('ALTER TABLE conversation ADD is_group BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE conversation DROP type');
        $this->addSql('ALTER TABLE conversation_participant ADD role VARCHAR(20) DEFAULT \'member\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant DROP created_at');
        $this->addSql('ALTER TABLE conversation_participant DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE group_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE group_message (id SERIAL NOT NULL, author_id INT NOT NULL, group_id INT NOT NULL, type VARCHAR(20) NOT NULL, content TEXT DEFAULT NULL, track JSON DEFAULT NULL, track_metadata JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_30bd6473f675f31b ON group_message (author_id)');
        $this->addSql('COMMENT ON COLUMN group_message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN group_message.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE group_message ADD CONSTRAINT fk_30bd6473f675f31b FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversation ADD type VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE conversation DROP is_group');
        $this->addSql('ALTER TABLE conversation_participant ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE conversation_participant DROP role');
        $this->addSql('COMMENT ON COLUMN conversation_participant.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN conversation_participant.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
