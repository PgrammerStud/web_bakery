<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527000002 extends AbstractMigration  
{
    public function getDescription(): string
    {
        return 'Add rider_id column to delivery table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE delivery ADD rider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_delivery_rider FOREIGN KEY (rider_id) REFERENCES `user`(id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_delivery_rider ON delivery (rider_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_delivery_rider');
        $this->addSql('DROP INDEX IDX_delivery_rider ON delivery');
        $this->addSql('ALTER TABLE delivery DROP COLUMN rider_id');
    }
}