<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200914093941 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emails ADD email_content_id INT NOT NULL');
        $this->addSql('ALTER TABLE emails ADD CONSTRAINT FK_4C81E85228135132 FOREIGN KEY (email_content_id) REFERENCES email_content (id)');
        $this->addSql('CREATE INDEX IDX_4C81E85228135132 ON emails (email_content_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emails DROP FOREIGN KEY FK_4C81E85228135132');
        $this->addSql('DROP INDEX IDX_4C81E85228135132 ON emails');
        $this->addSql('ALTER TABLE emails DROP email_content_id');
    }
}
