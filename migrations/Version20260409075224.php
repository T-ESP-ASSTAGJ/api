<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409075224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reference Track in Message';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message ADD track_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message DROP track');
        $this->addSql('ALTER TABLE message DROP track_metadata');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6BD307F5ED23C43 ON message (track_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F5ED23C43');
        $this->addSql('DROP INDEX IDX_B6BD307F5ED23C43');
        $this->addSql('ALTER TABLE message ADD track JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD track_metadata JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE message DROP track_id');
    }
}
