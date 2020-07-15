<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200712112108 extends AbstractMigration
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
        $this->addSql('ALTER TABLE request_organisation_document DROP user_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_document ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A669C0FDA76ED395 ON request_organisation_document (user_id)');
    }
}
