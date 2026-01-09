<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214063823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update existing users with NULL or empty status to active';
    }

    public function up(Schema $schema): void
    {
        // Update existing users with NULL or empty status to 'active'
        $this->addSql('UPDATE user SET status = \'active\' WHERE status IS NULL OR status = \'\'');
    }

    public function down(Schema $schema): void
    {
        // This migration is not reversible as it would require knowing which users were originally NULL/empty
    }
}
