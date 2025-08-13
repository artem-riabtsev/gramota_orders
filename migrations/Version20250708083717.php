<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708083717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD surname VARCHAR(255) NOT NULL, ADD name VARCHAR(255) NOT NULL, ADD patronymic VARCHAR(255) DEFAULT NULL, ADD email VARCHAR(255) NOT NULL, ADD phone VARCHAR(255) NOT NULL, ADD is_approved TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649444F97DD ON user (phone)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D649E7927C74 ON user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D649444F97DD ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP surname, DROP name, DROP patronymic, DROP email, DROP phone, DROP is_approved
        SQL);
    }
}
