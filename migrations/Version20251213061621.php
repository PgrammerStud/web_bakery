<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251213061621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(255) NOT NULL, customer_name VARCHAR(255) NOT NULL, custome_contact VARCHAR(11) NOT NULL, status VARCHAR(255) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, payment_method VARCHAR(255) NOT NULL, notes VARCHAR(255) DEFAULT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_F5299398B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, price NUMERIC(10, 2) NOT NULL, subtotal NUMERIC(10, 2) NOT NULL, order_entity_id INT DEFAULT NULL, product_id INT DEFAULT NULL, order_property_id INT DEFAULT NULL, INDEX IDX_62809DB03DA206A5 (order_entity_id), INDEX IDX_62809DB04584665A (product_id), INDEX IDX_62809DB04F206C6A (order_property_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB03DA206A5 FOREIGN KEY (order_entity_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB04584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB04F206C6A FOREIGN KEY (order_property_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398B03A8386');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB03DA206A5');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB04584665A');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB04F206C6A');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_items');
    }
}
