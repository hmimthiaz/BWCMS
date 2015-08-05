<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150805143317 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE BWAudit (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:guid)\', level VARCHAR(50) NOT NULL, remoteAddress VARCHAR(50) NOT NULL, logDate DATETIME NOT NULL, module VARCHAR(255) NOT NULL, guid VARCHAR(255) DEFAULT NULL, action VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, userId VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_CA88A40164B64DCC (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE BWAudit ADD CONSTRAINT FK_CA88A40164B64DCC FOREIGN KEY (userId) REFERENCES BWUser (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE BWAudit');
    }
}
