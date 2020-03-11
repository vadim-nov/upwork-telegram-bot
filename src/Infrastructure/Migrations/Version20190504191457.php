<?php

declare(strict_types=1);

namespace App\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190504191457 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_search DROP FOREIGN KEY FK_D1A2C09AA76ED395');
        $this->addSql('ALTER TABLE user_search ADD CONSTRAINT FK_D1A2C09AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE upwork_job DROP FOREIGN KEY FK_E7DD8D7265E7ED4D');
        $this->addSql('ALTER TABLE upwork_job DROP FOREIGN KEY FK_E7DD8D72A76ED395');
        $this->addSql('ALTER TABLE upwork_job ADD CONSTRAINT FK_E7DD8D7265E7ED4D FOREIGN KEY (user_search_id) REFERENCES user_search (id)');
        $this->addSql('ALTER TABLE upwork_job ADD CONSTRAINT FK_E7DD8D72A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE upwork_job DROP FOREIGN KEY FK_E7DD8D7265E7ED4D');
        $this->addSql('ALTER TABLE upwork_job DROP FOREIGN KEY FK_E7DD8D72A76ED395');
        $this->addSql('ALTER TABLE upwork_job ADD CONSTRAINT FK_E7DD8D7265E7ED4D FOREIGN KEY (user_search_id) REFERENCES user_search (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE upwork_job ADD CONSTRAINT FK_E7DD8D72A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_search DROP FOREIGN KEY FK_D1A2C09AA76ED395');
        $this->addSql('ALTER TABLE user_search ADD CONSTRAINT FK_D1A2C09AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
