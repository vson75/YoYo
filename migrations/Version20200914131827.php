<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200914131827 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emails ADD user_recipient_id INT');
        $this->addSql('ALTER TABLE emails ADD CONSTRAINT FK_4C81E85269E3F37A FOREIGN KEY (user_recipient_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4C81E85269E3F37A ON emails (user_recipient_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emails DROP FOREIGN KEY FK_4C81E85269E3F37A');
        $this->addSql('DROP INDEX IDX_4C81E85269E3F37A ON emails');
        $this->addSql('ALTER TABLE emails DROP user_recipient_id');
    }
}
