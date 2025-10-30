<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251030143837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update follow table column name from followed_id to followed_user_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT fk_68344470d956f010');
        $this->addSql('DROP INDEX idx_68344470d956f010');
        $this->addSql('ALTER TABLE follow RENAME COLUMN followed_id TO followed_user_id');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AF2612FD FOREIGN KEY (followed_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_68344470AF2612FD ON follow (followed_user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE follow DROP CONSTRAINT FK_68344470AF2612FD');
        $this->addSql('DROP INDEX IDX_68344470AF2612FD');
        $this->addSql('ALTER TABLE follow RENAME COLUMN followed_user_id TO followed_id');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT fk_68344470d956f010 FOREIGN KEY (followed_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_68344470d956f010 ON follow (followed_id)');
    }
}
