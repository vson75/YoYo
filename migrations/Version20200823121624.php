<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200823121624 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_document (id INT AUTO_INCREMENT NOT NULL, post_id INT DEFAULT NULL, document_type_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, deposite_date DATE DEFAULT NULL, INDEX IDX_678403D74B89032C (post_id), INDEX IDX_678403D761232A4F (document_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_document ADD CONSTRAINT FK_678403D74B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_document ADD CONSTRAINT FK_678403D761232A4F FOREIGN KEY (document_type_id) REFERENCES document_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_document');
    }
}
