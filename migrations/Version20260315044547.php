<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315044547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bakeitforwardwallet (id INT AUTO_INCREMENT NOT NULL, total_balance DOUBLE PRECISION NOT NULL, goal_amount DOUBLE PRECISION NOT NULL, last_updated DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE delivery (id INT AUTO_INCREMENT NOT NULL, delivery_address VARCHAR(255) NOT NULL, delivery_contact VARCHAR(255) NOT NULL, delivery_date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, delivery_fee DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, orders_id INT DEFAULT NULL, INDEX IDX_3781EC10CFFE9AD6 (orders_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE delivery ADD CONSTRAINT FK_3781EC10CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery DROP FOREIGN KEY FK_3781EC10CFFE9AD6');
        $this->addSql('DROP TABLE bakeitforwardwallet');
        $this->addSql('DROP TABLE delivery');
    }
}
