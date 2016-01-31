<?php

namespace Bellwether\BWCMSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManager;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;
use Bellwether\BWCMSBundle\Classes\Service\ContentQueryService;
use Bellwether\BWCMSBundle\Classes\Service\ThumbService;
use Bellwether\BWCMSBundle\Classes\Service\S3Service;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;
use Bellwether\BWCMSBundle\Entity\S3QueueEntity;
use Bellwether\BWCMSBundle\Entity\S3QueueRepository;


class S3UploadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('BWCMS:S3Upload')
            ->setDescription('Command to work with search index.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->getContainer()->get('BWCMS.KernelEventListener')->init();

        $s3Repo = $this->em()->getRepository('BWCMSBundle:S3QueueEntity');
        $qb = $s3Repo->createQueryBuilder('s');
        $qb->andWhere("s.status = 'Queue'");
        $qb->add('orderBy', 's.createdDate ASC');
        $qb->setFirstResult(0);
        $qb->setMaxResults(25);
        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var S3QueueEntity $s3QueueEntity
             */
            foreach ($result as $s3QueueEntity) {
                echo 'File: ' . $s3QueueEntity->getContent()->getTitle() . "\n";
                $this->s3Service()->processQueueItem($s3QueueEntity);
            }
        }
    }

    /**
     * @return \AppKernel
     */
    public function getKernel()
    {
        return $this->getContainer()->get('kernel');
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->getContainer()->get('BWCMS.Content')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->getContainer()->get('BWCMS.Media')->getManager();
    }

    /**
     * @return ContentQueryService
     */
    public function cq()
    {
        return $this->getContainer()->get('BWCMS.ContentQuery')->getManager();
    }

    /**
     * @return ThumbService
     */
    public function getThumbService()
    {
        return $this->getContainer()->get('BWCMS.Thumb');
    }

    /**
     * @return S3Service
     */
    public function s3Service()
    {
        return $this->getContainer()->get('BWCMS.S3')->getManager();
    }

    /**
     * @return S3QueueRepository
     */
    public function getS3Repository()
    {
        return $this->em()->getRepository('BWCMSBundle:S3QueueEntity');
    }



}