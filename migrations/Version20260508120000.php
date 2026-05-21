<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add Firebase authentication fields to User entity
 */
final class Version20260508120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Firebase authentication fields: firebaseUid, displayName, emailVerified, apiToken';
    }

    public function up(Schema $schema): void
    {
        // Add Firebase UID column
        $this->addSql('ALTER TABLE user ADD firebase_uid VARCHAR(255) NULL UNIQUE');
        
        // Add display name column
        $this->addSql('ALTER TABLE user ADD display_name VARCHAR(255) NULL');
        
        // Add email verified column
        $this->addSql('ALTER TABLE user ADD email_verified BOOLEAN NULL DEFAULT 0');
        
        // Add API token column
        $this->addSql('ALTER TABLE user ADD api_token VARCHAR(255) NULL UNIQUE');
    }

    public function down(Schema $schema): void
    {
        // Remove columns in reverse order
        $this->addSql('ALTER TABLE user DROP COLUMN api_token');
        $this->addSql('ALTER TABLE user DROP COLUMN email_verified');
        $this->addSql('ALTER TABLE user DROP COLUMN display_name');
        $this->addSql('ALTER TABLE user DROP COLUMN firebase_uid');
    }
}
