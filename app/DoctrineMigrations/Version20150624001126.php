<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150624001126 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;
    private $uploadFolder;
    private $webPath;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $rootDirectory = $this->getKernel()->getRootDir();
        $webRoot = realpath($rootDirectory . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');
        $this->webPath = $this->container->getParameter('media.path');
        $this->uploadFolder = $webRoot . DIRECTORY_SEPARATOR . $this->webPath;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE BWContentMedia (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:guid)\', file VARCHAR(100) DEFAULT NULL, mime VARCHAR(100) DEFAULT NULL, extension VARCHAR(100) DEFAULT NULL, size BIGINT DEFAULT NULL, height INT DEFAULT NULL, width INT DEFAULT NULL, data LONGBLOB DEFAULT NULL, contentId VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_5DDB75F373A18A3B (contentId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE BWContentMedia ADD CONSTRAINT FK_5DDB75F373A18A3B FOREIGN KEY (contentId) REFERENCES BWContent (id)');
        $this->addSql('ALTER TABLE BWContent CHANGE scope scope VARCHAR(100) NOT NULL');

    }

    public function postUp(Schema $schema)
    {
        $this->cm()->init();
        $contentRepo = $this->cm()->getContentRepository();
        $allContent = $contentRepo->findAll();
        if (!empty($allContent)) {
            /**
             * @var ContentEntity $content
             */
            foreach ($allContent as $content) {
                if (!is_null($content->getFile())) {
                    print "Importing: " . $content->getFile();
                    $contentMedia = new ContentMediaEntity();
                    $contentMedia->setFile($content->getFile());
                    $contentMedia->setExtension($content->getExtension());
                    $contentMedia->setMime($content->getMime());
                    $contentMedia->setSize($content->getSize());
                    $contentMedia->setHeight($content->getHeight());
                    $contentMedia->setWidth($content->getWidth());
                    $mediaFile = $this->getFilePath($content->getFile(), true);
                    if (file_exists($mediaFile)) {
                        $mediaStream = fopen($mediaFile, 'rb');
                        $contentMedia->setData(stream_get_contents($mediaStream));
                        fclose($mediaStream);
                        print "  ...";
                    }
                    $contentMedia->setContent($content);
                    $this->em()->persist($contentMedia);
                    $this->em()->flush();
                    print " Ok\n";
                }
            }
            $this->em()->flush();
        }

    }

    /**
     * @param $filename
     * @return bool|string
     */
    public function getFilePath($filename, $fullPath = false)
    {
        if (empty ($filename)) {
            return false;
        }
        if (preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})_/", $filename, $regs)) {
            $filePath = $regs [1] . DIRECTORY_SEPARATOR .
                $regs [2] . DIRECTORY_SEPARATOR .
                $regs [3] . DIRECTORY_SEPARATOR . $filename;
            if ($fullPath) {
                return $this->uploadFolder . DIRECTORY_SEPARATOR . $filePath;
            }
            return $this->webPath . DIRECTORY_SEPARATOR . $filePath;
        } else {
            return false;
        }
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE BWContentMedia');
        $this->addSql('ALTER TABLE BWContent CHANGE scope scope VARCHAR(100) DEFAULT \'CS.CPublic\' NOT NULL');
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return \AppKernel
     */
    public function getKernel(){
        return $this->container->get( 'kernel' );
    }

}
