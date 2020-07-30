<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200730124034 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document DROP FOREIGN KEY FK_A669C0FDA76ED395');
        $this->addSql('DROP INDEX IDX_A669C0FDA76ED395 ON request_organisation_document');
        $this->addSql('ALTER TABLE request_organisation_document ADD user VARCHAR(255) NOT NULL, DROP user_id');
        $this->addSql('ALTER TABLE request_organisation_info DROP FOREIGN KEY FK_1AA14D53A76ED395');
        $this->addSql('DROP INDEX UNIQ_1AA14D53A76ED395 ON request_organisation_info');
        $this->addSql('ALTER TABLE request_organisation_info ADD user VARCHAR(255) NOT NULL, DROP user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document ADD user_id INT DEFAULT NULL, DROP user');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A669C0FDA76ED395 ON request_organisation_document (user_id)');
        $this->addSql('ALTER TABLE request_organisation_info ADD user_id INT DEFAULT NULL, DROP user');
        $this->addSql('ALTER TABLE request_organisation_info ADD CONSTRAINT FK_1AA14D53A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1AA14D53A76ED395 ON request_organisation_info (user_id)');
    }
}
