<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710183342 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisation_document DROP FOREIGN KEY FK_41035FBB9D86650F');
        $this->addSql('DROP INDEX IDX_41035FBB9D86650F ON organisation_document');
        $this->addSql('ALTER TABLE organisation_document CHANGE user_id_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE organisation_document ADD CONSTRAINT FK_41035FBBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_41035FBBA76ED395 ON organisation_document (user_id)');
        $this->addSql('ALTER TABLE request_organisation_document DROP FOREIGN KEY FK_A669C0FD9D86650F');
        $this->addSql('DROP INDEX IDX_A669C0FD9D86650F ON request_organisation_document');
        $this->addSql('ALTER TABLE request_organisation_document CHANGE user_id_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A669C0FDA76ED395 ON request_organisation_document (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisation_document DROP FOREIGN KEY FK_41035FBBA76ED395');
        $this->addSql('DROP INDEX IDX_41035FBBA76ED395 ON organisation_document');
        $this->addSql('ALTER TABLE organisation_document CHANGE user_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE organisation_document ADD CONSTRAINT FK_41035FBB9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_41035FBB9D86650F ON organisation_document (user_id_id)');
        $this->addSql('ALTER TABLE request_organisation_document DROP FOREIGN KEY FK_A669C0FDA76ED395');
        $this->addSql('DROP INDEX IDX_A669C0FDA76ED395 ON request_organisation_document');
        $this->addSql('ALTER TABLE request_organisation_document CHANGE user_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_document ADD CONSTRAINT FK_A669C0FD9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A669C0FD9D86650F ON request_organisation_document (user_id_id)');
    }
}
