<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905133924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_date_historic (id INT AUTO_INCREMENT NOT NULL, post_id INT DEFAULT NULL, post_date_type_id INT DEFAULT NULL, user_id INT NOT NULL, date DATE DEFAULT NULL, INDEX IDX_711638E34B89032C (post_id), INDEX IDX_711638E35796F6BD (post_date_type_id), INDEX IDX_711638E3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_date_historic ADD CONSTRAINT FK_711638E34B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_date_historic ADD CONSTRAINT FK_711638E35796F6BD FOREIGN KEY (post_date_type_id) REFERENCES post_date_type (id)');
        $this->addSql('ALTER TABLE post_date_historic ADD CONSTRAINT FK_711638E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE post_date_historic');
    }
}
