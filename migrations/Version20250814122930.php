<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250814122930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE status status INT NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE price CHANGE product_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE date date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE price CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` CHANGE date date DATE NOT NULL, CHANGE status status INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE date date DATE NOT NULL');
    }
}
