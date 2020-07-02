<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200702162340 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_document ADD document_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_document ADD CONSTRAINT FK_38E46E7661232A4F FOREIGN KEY (document_type_id) REFERENCES document_type (id)');
        $this->addSql('CREATE INDEX IDX_38E46E7661232A4F ON user_document (document_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_document DROP FOREIGN KEY FK_38E46E7661232A4F');
        $this->addSql('DROP INDEX IDX_38E46E7661232A4F ON user_document');
        $this->addSql('ALTER TABLE user_document DROP document_type_id');
    }
}
