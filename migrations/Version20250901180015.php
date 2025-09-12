<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250901180015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove duplicate unique index, set password nullable, add phone number to user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD phone_number VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER is_confirmed SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6496B01BC5B ON "user" (phone_number)');
        $this->addSql('ALTER INDEX uniq_identifier_username RENAME TO UNIQ_8D93D649F85E0677');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_8D93D6496B01BC5B');
        $this->addSql('ALTER TABLE "user" DROP phone_number');
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER is_confirmed DROP NOT NULL');
        $this->addSql('ALTER INDEX uniq_8d93d649f85e0677 RENAME TO uniq_identifier_username');
    }
}
