<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250111135103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire ADD id INT AUTO_INCREMENT NOT NULL, ADD user_id INT DEFAULT NULL, ADD article_id INT DEFAULT NULL, ADD content LONGTEXT NOT NULL, ADD date_commentaire DATETIME NOT NULL, DROP i, ADD PRIMARY KEY (id)');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC7294869C ON commentaire (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentaire MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC7294869C');
        $this->addSql('DROP INDEX IDX_67F068BCA76ED395 ON commentaire');
        $this->addSql('DROP INDEX IDX_67F068BC7294869C ON commentaire');
        $this->addSql('DROP INDEX `primary` ON commentaire');
        $this->addSql('ALTER TABLE commentaire ADD i INT NOT NULL, DROP id, DROP user_id, DROP article_id, DROP content, DROP date_commentaire');
    }
}
