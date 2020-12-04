<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201204132916 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD association_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610EFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id)');
        $this->addSql('CREATE INDEX IDX_8C9F3610EFB9C8A5 ON file (association_id)');
        $this->addSql('ALTER TABLE image ADD association_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FEFB9C8A5 FOREIGN KEY (association_id) REFERENCES association (id)');
        $this->addSql('CREATE INDEX IDX_C53D045FEFB9C8A5 ON image (association_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610EFB9C8A5');
        $this->addSql('DROP INDEX IDX_8C9F3610EFB9C8A5 ON file');
        $this->addSql('ALTER TABLE file DROP association_id');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FEFB9C8A5');
        $this->addSql('DROP INDEX IDX_C53D045FEFB9C8A5 ON image');
        $this->addSql('ALTER TABLE image DROP association_id');
    }
}
