<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200914091028 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE email_content (id INT AUTO_INCREMENT NOT NULL, object LONGTEXT NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE emails ADD email_content_id INT NOT NULL, DROP object, DROP content, CHANGE destination email_destination VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE emails ADD CONSTRAINT FK_4C81E85228135132 FOREIGN KEY (email_content_id) REFERENCES email_content (id)');
        $this->addSql('CREATE INDEX IDX_4C81E85228135132 ON emails (email_content_id)');
        $this->addSql('ALTER TABLE post_document ADD email_content_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_document ADD CONSTRAINT FK_678403D728135132 FOREIGN KEY (email_content_id) REFERENCES email_content (id)');
        $this->addSql('CREATE INDEX IDX_678403D728135132 ON post_document (email_content_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emails DROP FOREIGN KEY FK_4C81E85228135132');
        $this->addSql('ALTER TABLE post_document DROP FOREIGN KEY FK_678403D728135132');
        $this->addSql('DROP TABLE email_content');
        $this->addSql('DROP INDEX IDX_4C81E85228135132 ON emails');
        $this->addSql('ALTER TABLE emails ADD object LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP email_content_id, CHANGE email_destination destination VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX IDX_678403D728135132 ON post_document');
        $this->addSql('ALTER TABLE post_document DROP email_content_id');
    }
}
