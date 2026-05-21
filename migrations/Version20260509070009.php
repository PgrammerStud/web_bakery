<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509070009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE lastname lastname VARCHAR(255) DEFAULT NULL, CHANGE firstname firstname VARCHAR(255) DEFAULT NULL, CHANGE email_verified email_verified TINYINT DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX firebase_uid TO UNIQ_8D93D6492FB49151');
        $this->addSql('ALTER TABLE user RENAME INDEX api_token TO UNIQ_8D93D6497BA2F5EB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user CHANGE email_verified email_verified TINYINT DEFAULT 0, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d6497ba2f5eb TO api_token');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d6492fb49151 TO firebase_uid');
    }
}
