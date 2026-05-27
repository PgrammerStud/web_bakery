<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add address and contact_number to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ADD address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD contact_number VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` DROP COLUMN address');
        $this->addSql('ALTER TABLE `user` DROP COLUMN contact_number');
    }
}