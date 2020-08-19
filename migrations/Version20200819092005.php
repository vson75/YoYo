<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200819092005 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_parameter ADD variable_fees DOUBLE PRECISION DEFAULT NULL, ADD fixed_fees DOUBLE PRECISION DEFAULT NULL, ADD email LONGTEXT DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD code_postal LONGTEXT DEFAULT NULL, ADD city VARCHAR(100) DEFAULT NULL, ADD country VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_parameter DROP variable_fees, DROP fixed_fees, DROP email, DROP address, DROP code_postal, DROP city, DROP country');
    }
}
