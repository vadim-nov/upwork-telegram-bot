<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190419153305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_user_link ON user_rss_settings');
        $this->addSql('ALTER TABLE user_rss_settings ADD search_url VARCHAR(500) DEFAULT NULL, CHANGE link rss_link VARCHAR(500) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_user_link ON user_rss_settings (user_id, rss_link)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX unique_user_link ON user_rss_settings');
        $this->addSql('ALTER TABLE user_rss_settings ADD link VARCHAR(500) DEFAULT NULL COLLATE utf8mb4_unicode_ci, DROP rss_link, DROP search_url');
        $this->addSql('CREATE UNIQUE INDEX unique_user_link ON user_rss_settings (user_id, link)');
    }
}
