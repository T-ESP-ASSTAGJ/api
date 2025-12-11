<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251211090820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add comment_count for post';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD comments_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post DROP comments_count');
    }
}
