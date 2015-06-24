<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150615000000 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE BWContentRelation (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:guid)\', relation VARCHAR(100) NOT NULL, contentId VARCHAR(255) NOT NULL COMMENT \'(DC2Type:guid)\', relatedContentId VARCHAR(255) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_1A9C143773A18A3B (contentId), INDEX IDX_1A9C143779130355 (relatedContentId), UNIQUE INDEX ix_contentId_relation_relatedContentId (contentId, relation, relatedContentId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE BWContentRelation ADD CONSTRAINT FK_1A9C143773A18A3B FOREIGN KEY (contentId) REFERENCES BWContent (id)');
        $this->addSql('ALTER TABLE BWContentRelation ADD CONSTRAINT FK_1A9C143779130355 FOREIGN KEY (relatedContentId) REFERENCES BWContent (id)');
        $this->addSql('ALTER TABLE BWContent ADD lastModifiedAuthorId VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE BWContent ADD CONSTRAINT FK_E59A13BBB13B34DB FOREIGN KEY (lastModifiedAuthorId) REFERENCES BWUser (id)');
        $this->addSql('CREATE INDEX IDX_E59A13BBB13B34DB ON BWContent (lastModifiedAuthorId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE BWContentRelation');
        $this->addSql('ALTER TABLE BWContent DROP FOREIGN KEY FK_E59A13BBB13B34DB');
        $this->addSql('DROP INDEX IDX_E59A13BBB13B34DB ON BWContent');
        $this->addSql('ALTER TABLE BWContent DROP lastModifiedAuthorId');
    }
}
