<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200822192624 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD user_validator_id INT DEFAULT NULL, ADD date_validation DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D30A836EE FOREIGN KEY (user_validator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D30A836EE ON post (user_validator_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D30A836EE');
        $this->addSql('DROP INDEX IDX_5A8A6C8D30A836EE ON post');
        $this->addSql('ALTER TABLE post DROP user_validator_id, DROP date_validation');
    }
}
