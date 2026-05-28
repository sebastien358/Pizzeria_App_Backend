<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528185826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE command ADD is_read TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE contact ADD is_read TINYINT(1) NOT NULL, CHANGE message message VARCHAR(1500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact DROP is_read, CHANGE message message VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE command DROP is_read');
    }
}
