<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710164108 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organisation_document (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, document_type_id_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, INDEX IDX_41035FBB9D86650F (user_id_id), INDEX IDX_41035FBBD08BB2F6 (document_type_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisation_info (id INT AUTO_INCREMENT NOT NULL, organisation_name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) NOT NULL, string VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, phone_number VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_organisation_document (id INT AUTO_INCREMENT NOT NULL, user_id_id INT DEFAULT NULL, document_type_id_id INT DEFAULT NULL, filename VARCHAR(255) DEFAULT NULL, original_filename VARCHAR(255) DEFAULT NULL, deposite_date DATETIME DEFAULT NULL, mime_type VARCHAR(100) DEFAULT NULL, INDEX IDX_A669C0FD9D86650F (user_id_id), UNIQUE INDEX UNIQ_A669C0FDD08BB2F6 (document_type_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_organisation_info (id INT AUTO_INCREMENT NOT NULL, organisation_name VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(100) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, country VARCHAR(10) DEFAULT NULL, phone_number VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE organisation_document ADD CONSTRAINT FK_41035FBB9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE organisation_document ADD CONSTRAINT FK_41035FBBD08BB2F6 FOREIGN KEY (document_type_id_id) REFERENCES document_type (id)');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FD9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FDD08BB2F6 FOREIGN KEY (document_type_id_id) REFERENCES document_type (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE organisation_document');
        $this->addSql('DROP TABLE organisation_info');
        $this->addSql('DROP TABLE request_organisation_document');
        $this->addSql('DROP TABLE request_organisation_info');
    }
}
