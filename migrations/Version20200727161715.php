<?php

declare(strict_types=1);


namespace DoctrineMigrations;


/**
final class Version20200727161715 extends AbstractMigration
{
public function getDescription() : string
{
return '';
}

public function up(Schema $schema) : void
{
// this up() migration is auto-generated, please modify it to your needs
$this->addSql('ALTER TABLE transaction DROP a?mount_after_fees');
}

public function down(Schema $schema) : void
{
// this down() migration is auto-generated, please modify it to yoneeds
$this->addSql('ALTER TABLE transaction ADD a?mount_after_fees DOUBLE PRECISION DEFAULT NULL');
}
}

 *
 */
