<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710184724 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE request_status (id INT AUTO_INCREMENT NOT NULL, request_status VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request_organisation_document ADD request_status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FD2006F11A FOREIGN KEY (request_status_id) REFERENCES request_status (id)');
        $this->addSql('CREATE INDEX IDX_A669C0FD2006F11A ON request_organisation_document (request_status_id)');
        $this->addSql('ALTER TABLE request_organisation_info ADD request_status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_info ADD CONSTRAINT FK_1AA14D532006F11A FOREIGN KEY (request_status_id) REFERENCES request_status (id)');
        $this->addSql('CREATE INDEX IDX_1AA14D532006F11A ON request_organisation_info (request_status_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document DROP FOREIGN KEY FK_A669C0FD2006F11A');
        $this->addSql('ALTER TABLE request_organisation_info DROP FOREIGN KEY FK_1AA14D532006F11A');
        $this->addSql('DROP TABLE request_status');
        $this->addSql('DROP INDEX IDX_A669C0FD2006F11A ON request_organisation_document');
        $this->addSql('ALTER TABLE request_organisation_document DROP request_status_id');
        $this->addSql('DROP INDEX IDX_1AA14D532006F11A ON request_organisation_info');
        $this->addSql('ALTER TABLE request_organisation_info DROP request_status_id');
    }
}
