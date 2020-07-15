<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710193938 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document DROP FOREIGN KEY FK_A669C0FDD08BB2F6');
        $this->addSql('DROP INDEX UNIQ_A669C0FDD08BB2F6 ON request_organisation_document');
        $this->addSql('ALTER TABLE request_organisation_document DROP document_type_id_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document ADD document_type_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FDD08BB2F6 FOREIGN KEY (document_type_id_id) REFERENCES document_type (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A669C0FDD08BB2F6 ON request_organisation_document (document_type_id_id)');
    }
}
