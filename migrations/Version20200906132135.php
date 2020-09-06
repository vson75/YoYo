<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200906132135 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_date_historic ADD post_document_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_date_historic ADD CONSTRAINT FK_711638E32F7FF218 FOREIGN KEY (post_document_id) REFERENCES post_document (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_711638E32F7FF218 ON post_date_historic (post_document_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_date_historic DROP FOREIGN KEY FK_711638E32F7FF218');
        $this->addSql('DROP INDEX UNIQ_711638E32F7FF218 ON post_date_historic');
        $this->addSql('ALTER TABLE post_date_historic DROP post_document_id');
    }
}
