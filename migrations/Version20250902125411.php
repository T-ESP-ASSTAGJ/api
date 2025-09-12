<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250902125411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add verification_user table to store email verification codes for magic link authentication.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE verification_user (id SERIAL NOT NULL, email VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12A7254EE7927C74 ON verification_user (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE verification_user');
    }
}
