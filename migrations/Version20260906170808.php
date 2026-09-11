<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260906170808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_request (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(30) DEFAULT NULL, message LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, destination_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_A1B8AE1E816C6140 (destination_id), INDEX IDX_A1B8AE1EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_destination (user_id INT NOT NULL, destination_id INT NOT NULL, INDEX IDX_97DDF73FA76ED395 (user_id), INDEX IDX_97DDF73F816C6140 (destination_id), PRIMARY KEY (user_id, destination_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE contact_request ADD CONSTRAINT FK_A1B8AE1E816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contact_request ADD CONSTRAINT FK_A1B8AE1EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_destination ADD CONSTRAINT FK_97DDF73FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_destination ADD CONSTRAINT FK_97DDF73F816C6140 FOREIGN KEY (destination_id) REFERENCES destination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP active');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_request DROP FOREIGN KEY FK_A1B8AE1E816C6140');
        $this->addSql('ALTER TABLE contact_request DROP FOREIGN KEY FK_A1B8AE1EA76ED395');
        $this->addSql('ALTER TABLE user_destination DROP FOREIGN KEY FK_97DDF73FA76ED395');
        $this->addSql('ALTER TABLE user_destination DROP FOREIGN KEY FK_97DDF73F816C6140');
        $this->addSql('DROP TABLE contact_request');
        $this->addSql('DROP TABLE user_destination');
        $this->addSql('ALTER TABLE `user` ADD active TINYINT DEFAULT 1 NOT NULL');
    }
}
