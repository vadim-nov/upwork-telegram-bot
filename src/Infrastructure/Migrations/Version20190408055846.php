<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190408055846 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (id VARCHAR(255) NOT NULL, telegram_ref INT NOT NULL, username VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, is_bot TINYINT(1) NOT NULL, is_enabled TINYINT(1) NOT NULL, is_premium TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_rss_settings (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) DEFAULT NULL, link LONGTEXT NOT NULL, processed_json LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_645A5E64A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_rss_settings ADD CONSTRAINT FK_645A5E64A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_rss_settings DROP FOREIGN KEY FK_645A5E64A76ED395');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_rss_settings');
    }
}
