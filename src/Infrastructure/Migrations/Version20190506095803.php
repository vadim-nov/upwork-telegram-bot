<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190506095803 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('UPDATE user SET username=telegram_ref WHERE telegram_ref IS NOT NULL');
        $this->addSql('ALTER TABLE user ADD email VARCHAR(255) DEFAULT NULL, ADD name VARCHAR(255) DEFAULT NULL, ADD facebook_client_id VARCHAR(255) DEFAULT NULL, ADD google_client_id VARCHAR(255) DEFAULT NULL, DROP first_name, DROP last_name, CHANGE username username VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('UPDATE user SET email=username WHERE telegram_ref IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD last_name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP email, DROP name, DROP facebook_client_id, DROP google_client_id, CHANGE username username VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
