<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190418104520 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD current_plan_id VARCHAR(255) DEFAULT NULL, ADD current_plan_from DATETIME DEFAULT NULL, ADD current_plan_to DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494294871E FOREIGN KEY (current_plan_id) REFERENCES plan (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6494294871E ON user (current_plan_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494294871E');
        $this->addSql('DROP INDEX IDX_8D93D6494294871E ON user');
        $this->addSql('ALTER TABLE user DROP current_plan_id, DROP current_plan_from, DROP current_plan_to');
    }
}
