<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601071355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE tesmimonial');
        $this->addSql('ALTER TABLE picture ADD testimonial_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F891D4EC6B1 FOREIGN KEY (testimonial_id) REFERENCES testimonial (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_16DB4F891D4EC6B1 ON picture (testimonial_id)');
        $this->addSql('ALTER TABLE testimonial ADD firstname VARCHAR(125) NOT NULL, ADD lastname VARCHAR(125) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP author');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tesmimonial (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F891D4EC6B1');
        $this->addSql('DROP INDEX IDX_16DB4F891D4EC6B1 ON picture');
        $this->addSql('ALTER TABLE picture DROP testimonial_id');
        $this->addSql('ALTER TABLE testimonial ADD author VARCHAR(200) NOT NULL, DROP firstname, DROP lastname, DROP created_at');
    }
}
