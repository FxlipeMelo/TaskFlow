<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260704012955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_workspace (user_id INT NOT NULL, workspace_id INT NOT NULL, INDEX IDX_8D748DFDA76ED395 (user_id), INDEX IDX_8D748DFD82D40A1F (workspace_id), PRIMARY KEY (user_id, workspace_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE workspace (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_workspace ADD CONSTRAINT FK_8D748DFDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_workspace ADD CONSTRAINT FK_8D748DFD82D40A1F FOREIGN KEY (workspace_id) REFERENCES workspace (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD workspace_id INT NOT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2582D40A1F FOREIGN KEY (workspace_id) REFERENCES workspace (id)');
        $this->addSql('CREATE INDEX IDX_527EDB2582D40A1F ON task (workspace_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_workspace DROP FOREIGN KEY FK_8D748DFDA76ED395');
        $this->addSql('ALTER TABLE user_workspace DROP FOREIGN KEY FK_8D748DFD82D40A1F');
        $this->addSql('DROP TABLE user_workspace');
        $this->addSql('DROP TABLE workspace');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2582D40A1F');
        $this->addSql('DROP INDEX IDX_527EDB2582D40A1F ON task');
        $this->addSql('ALTER TABLE task DROP workspace_id');
    }
}
