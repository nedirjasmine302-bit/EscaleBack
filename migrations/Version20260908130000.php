<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Passe l'image de la destination en LONGTEXT pour stocker le base64 en base.
 */
final class Version20260908130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Image de la destination stockée en base64 (LONGTEXT)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE destination CHANGE image image LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE destination CHANGE image image VARCHAR(255) DEFAULT NULL');
    }
}
