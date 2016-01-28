<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;
use Bellwether\BWCMSBundle\Entity\S3QueueEntity;
use Bellwether\BWCMSBundle\Entity\S3QueueRepository;


class S3Service extends BaseService
{

    private $enabled;
    private $bucketName;
    private $domain;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return S3Service
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * Service Init.
     */
    public function init()
    {
        if (!$this->loaded) {
            $this->enabled = (bool)$this->container->getParameter('media.s3Enabled');
            $this->bucketName = $this->container->getParameter('media.s3Bucket');
            $this->domain = $this->container->getParameter('media.s3Domain');
        }
        $this->loaded = true;
    }

    /**
     * @param ContentEntity $contentEntity
     * @return null
     */
    public function getImage($contentEntity)
    {
        if (!$this->enabled) {
            return null;
        }

        $s3QueueEntity = $this->getS3QueueItem($contentEntity);

        return null;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param string $thumbSlug
     * @param float $scaleFactor
     * @return S3QueueEntity
     */
    public function getThumbImage($contentEntity, $thumbSlug, $scaleFactor = 1.0)
    {
        if (!$this->enabled) {
            return null;
        }

        $s3QueueEntity = $this->getS3QueueItem($contentEntity, $thumbSlug, $scaleFactor);

        return null;
    }

    /**
     * @param ContentEntity $contentEntity
     * @return null
     */
    public function getContentDownloadLink($contentEntity)
    {
        if (!$this->enabled) {
            return null;
        }

        $s3QueueEntity = $this->getS3QueueItem($contentEntity);

        return null;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param string $thumbSlug
     * @param float $scaleFactor
     */
    public function getS3QueueItem($contentEntity, $thumbSlug = null, $scaleFactor = 1.0)
    {
        $cacheString = 'S3Queue_' . $contentEntity->getId() . '_' . $thumbSlug . '_' . (string)$scaleFactor;
        $s3QueueEntity = $this->cache()->fetch($cacheString);
        if (!empty($s3QueueEntity)) {
            return $s3QueueEntity;
        }

        $siteEntity = $this->sm()->getCurrentSite();

        $s3Repo = $this->getRepository();
        $qb = $s3Repo->createQueryBuilder('s');
        $qb->andWhere("s.content = '" . $contentEntity->getId() . "'");
        $qb->andWhere(" s.site ='" . $siteEntity->getId() . "' ");
        $qb->andWhere(" s.thumbScale ='" . (string)$scaleFactor . "' ");

        /**
         * @var ThumbStyleEntity $thumbEntity
         */
        $thumbEntity = null;
        if (!empty($thumbSlug)) {
            $thumbEntity = $this->mm()->getThumbStyle($thumbSlug, $this->sm()->getCurrentSite());
            $qb->andWhere(" s.thumStyle ='" . $thumbEntity->getId() . "' ");
        }else{
            $qb->andWhere(" s.thumStyle is NULL ");
        }

        try {
            $s3QueueEntity = $qb->getQuery()->getSingleResult();
            return $s3QueueEntity;
        } catch (\Doctrine\ORM\NoResultException $e) {

        }

        $s3QueueEntity = new S3QueueEntity();
        $s3QueueEntity->setContent($contentEntity);
        $s3QueueEntity->setSite($siteEntity);
        $s3QueueEntity->setThumStyle($thumbEntity);
        $s3QueueEntity->setThumbScale($scaleFactor);
        $s3QueueEntity->setStatus('Queue');
        $s3QueueEntity->setCreatedDate(new \DateTime());
        $this->em()->persist($s3QueueEntity);
        $this->em()->flush();

        return $s3QueueEntity;
    }


    /**
     * @return S3QueueRepository
     */
    public function getRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:S3QueueEntity');
    }
}
