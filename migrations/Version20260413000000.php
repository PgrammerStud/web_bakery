<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add CASCADE DELETE to delivery foreign key constraint
 */
final class Version20260413000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CASCADE DELETE to delivery orders_id foreign key';
    }

    public function up(Schema $schema): void
    {
        // Drop the old foreign key constraint
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10CFFE9AD6');
        
        // Add new foreign key with CASCADE DELETE
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop the new foreign key constraint
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10CFFE9AD6');
        
        // Restore the old foreign key without CASCADE DELETE
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
    }
}
