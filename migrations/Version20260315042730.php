<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260315042730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bakeitforward ADD amount DOUBLE PRECISION NOT NULL, ADD percentage DOUBLE PRECISION NOT NULL, ADD created_at DATETIME NOT NULL, ADD user_id INT DEFAULT NULL, ADD orders_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE bakeitforward ADD CONSTRAINT FK_34308FD5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE bakeitforward ADD CONSTRAINT FK_34308FD5CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_34308FD5A76ED395 ON bakeitforward (user_id)');
        $this->addSql('CREATE INDEX IDX_34308FD5CFFE9AD6 ON bakeitforward (orders_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bakeitforward DROP FOREIGN KEY FK_34308FD5A76ED395');
        $this->addSql('ALTER TABLE bakeitforward DROP FOREIGN KEY FK_34308FD5CFFE9AD6');
        $this->addSql('DROP INDEX IDX_34308FD5A76ED395 ON bakeitforward');
        $this->addSql('DROP INDEX IDX_34308FD5CFFE9AD6 ON bakeitforward');
        $this->addSql('ALTER TABLE bakeitforward DROP amount, DROP percentage, DROP created_at, DROP user_id, DROP orders_id');
    }
}
