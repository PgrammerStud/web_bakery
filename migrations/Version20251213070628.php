<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251213070628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE custome_contact customer_contact VARCHAR(11) NOT NULL');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY `FK_62809DB04F206C6A`');
        $this->addSql('DROP INDEX IDX_62809DB04F206C6A ON order_items');
        $this->addSql('ALTER TABLE order_items DROP order_property_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE customer_contact custome_contact VARCHAR(11) NOT NULL');
        $this->addSql('ALTER TABLE order_items ADD order_property_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT `FK_62809DB04F206C6A` FOREIGN KEY (order_property_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_62809DB04F206C6A ON order_items (order_property_id)');
    }
}
