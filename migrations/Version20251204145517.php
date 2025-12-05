<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204145517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_read and read_at columns to message table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD is_read BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE message ADD read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN message.read_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP is_read');
        $this->addSql('ALTER TABLE message DROP read_at');
    }
}
