<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200817195054 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_parameter ADD parameter_type_id INT DEFAULT NULL, ADD text_value LONGTEXT DEFAULT NULL, ADD float_value DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE admin_parameter ADD CONSTRAINT FK_C3609422F123013 FOREIGN KEY (parameter_type_id) REFERENCES parameter_type (id)');
        $this->addSql('CREATE INDEX IDX_C3609422F123013 ON admin_parameter (parameter_type_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_parameter DROP FOREIGN KEY FK_C3609422F123013');
        $this->addSql('DROP INDEX IDX_C3609422F123013 ON admin_parameter');
        $this->addSql('ALTER TABLE admin_parameter DROP parameter_type_id, DROP text_value, DROP float_value');
    }
}
