<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase fcm_token column length to 512';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user MODIFY fcm_token VARCHAR(512) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user MODIFY fcm_token VARCHAR(255) DEFAULT NULL');
    }
}