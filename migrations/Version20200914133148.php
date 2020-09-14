<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200914133148 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_content ADD post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE email_content ADD CONSTRAINT FK_430558664B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('CREATE INDEX IDX_430558664B89032C ON email_content (post_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_content DROP FOREIGN KEY FK_430558664B89032C');
        $this->addSql('DROP INDEX IDX_430558664B89032C ON email_content');
        $this->addSql('ALTER TABLE email_content DROP post_id');
    }
}
