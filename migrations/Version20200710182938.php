<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200710182938 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_info ADD user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE request_organisation_info ADD CONSTRAINT FK_1AA14D539D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1AA14D539D86650F ON request_organisation_info (user_id_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE request_organisation_info DROP FOREIGN KEY FK_1AA14D539D86650F');
        $this->addSql('DROP INDEX UNIQ_1AA14D539D86650F ON request_organisation_info');
        $this->addSql('ALTER TABLE request_organisation_info DROP user_id_id');
    }
}
