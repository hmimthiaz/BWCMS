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

use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service extends BaseService
{

    private $enabled;
    private $bucketName;
    private $pathPrefix;
    private $domainURLPrefix;

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
            $this->pathPrefix = $this->container->getParameter('media.s3Prefix');
            $this->domainURLPrefix = $this->container->getParameter('media.s3DomainURLPrefix');
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
        if ($s3QueueEntity->getStatus() != 'Done') {
            return null;
        }
        $cdnURL = $this->domainURLPrefix . '/' . $s3QueueEntity->getPath();
        return $cdnURL;
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
        if ($s3QueueEntity->getStatus() != 'Done') {
            return null;
        }
        $cdnURL = $this->domainURLPrefix . '/' . $s3QueueEntity->getPath();
        return $cdnURL;
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
        if ($s3QueueEntity->getStatus() != 'Done') {
            return null;
        }
        $cdnURL = $this->domainURLPrefix . '/' . $s3QueueEntity->getPath();
        return $cdnURL;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param string $thumbSlug
     * @param float $scaleFactor
     * @return S3QueueEntity|bool|mixed|string
     * @throws \Doctrine\ORM\NonUniqueResultException
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
            if (empty($thumbEntity)) {
                $thumbEntity = new ThumbStyleEntity();
                $thumbEntity->setSite($this->sm()->getCurrentSite());
                $thumbInfo = $this->tp()->getCurrentSkin()->getThumbStyleDefault($thumbSlug);
                if (!is_null($thumbInfo)) {
                    $thumbEntity->setName($thumbInfo['name']);
                    $thumbEntity->setSlug($thumbSlug);
                    $thumbEntity->setMode($thumbInfo['mode']);
                    $thumbEntity->setWidth($thumbInfo['width']);
                    $thumbEntity->setHeight($thumbInfo['height']);
                } else {
                    $thumbEntity->setName($thumbSlug);
                    $thumbEntity->setSlug($thumbSlug);
                    $thumbEntity->setMode('scaleResize');
                    $thumbEntity->setWidth(100);
                    $thumbEntity->setHeight(100);
                }
                $this->em()->persist($thumbEntity);
                $this->em()->flush();
            }
            $qb->andWhere(" s.thumStyle ='" . $thumbEntity->getId() . "' ");
        } else {
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
     * @param S3QueueEntity $s3QueueEntity
     */
    public function processQueueItem($s3QueueEntity)
    {
        if (is_null($s3QueueEntity->getThumStyle())) {
            $this->processContentItem($s3QueueEntity);
        } else {
            $this->processThumbItem($s3QueueEntity);
        }
    }

    /**
     * @param string $contentEntity
     * @return null
     */
    public function getUploadFileDownloadLink($filePath)
    {
        if (!$this->enabled) {
            return null;
        }

        $cdnURL = $this->domainURLPrefix . '/' . $filePath;
        return $cdnURL;
    }

    public function uploadFile(UploadedFile $uploadedFile)
    {
        $uploadedTempFile = $uploadedFile->getPathname();
        $originalName = $uploadedFile->getClientOriginalName();
        $filenameWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $originalName);
        $fileExtension = $uploadedFile->getClientOriginalExtension();
        $filename = $this->sanitizeFilename($filenameWithoutExt) . '.' . $fileExtension;
        $mimeType = $uploadedFile->getClientMimeType();
        $fileSize = $uploadedFile->getSize();

        $uploadDateTime = new \DateTime();
        $md5string = md5($uploadDateTime->format('Y-m-d H:i:s') . $filename);
        $s3Key = strtolower('uploads/' . $uploadDateTime->format('Y/m') . '/' . $md5string . '/' . $filename);


        $s3client = $this->s3();
        try {
            $result = $s3client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $s3Key,
                'SourceFile' => $uploadedTempFile,
                'CacheControl' => 'max-age=172800',
                "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years")),
                'ContentType' => $mimeType,
                'ACL' => 'public-read'
            ]);

            $returnData = array();
            $returnData['path'] = $s3Key;
            $returnData['filename'] = $filename;
            $returnData['mime'] = $mimeType;
            $returnData['size'] = $fileSize;
            $returnData['extension'] = $fileExtension;
            $returnData['dateTime'] = $uploadDateTime;

            return $returnData;
        } catch (\Aws\Exception\AwsException $e) {
            return $e;
        }
    }

    /**
     * @param S3QueueEntity $s3QueueEntity
     */
    private function processContentItem($s3QueueEntity)
    {
        $contentEntity = $s3QueueEntity->getContent();
        $siteEntity = $s3QueueEntity->getSite();
        $contentMediaEntity = $contentEntity->getMedia()->first();
        if (empty($contentMediaEntity)) {
            $s3QueueEntity->setStatus('Error');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        }
        $cacheFilename = $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
        $uploadDateTime = new \DateTime();
        $filename = $contentMediaEntity->getFile() . '.' . $contentMediaEntity->getExtension();
        $md5string = md5($uploadDateTime->format('Y-m-d H:i:s') . '(' . $contentMediaEntity->getId() . ')');
        $path = '/' . $siteEntity->getSlug() . '/media/' . $md5string . '/' . $filename;
        $s3Key = strtolower($this->pathPrefix . $path);

        $s3client = $this->s3();
        try {
            $result = $s3client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $s3Key,
                'SourceFile' => $cacheFilename,
                'CacheControl' => 'max-age=172800',
                "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years")),
                'ContentType' => $contentMediaEntity->getMime(),
                'ACL' => 'public-read'
            ]);

            $s3QueueEntity->setPrefix($this->pathPrefix);
            $s3QueueEntity->setPath($s3Key);
            $s3QueueEntity->setUploadedDate($uploadDateTime);
            $s3QueueEntity->setStatus('Done');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        } catch (\Aws\Exception\AwsException $e) {
            $s3QueueEntity->setStatus('Error');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        }
    }

    /**
     * @param S3QueueEntity $s3QueueEntity
     */
    private function processThumbItem($s3QueueEntity)
    {
        $contentEntity = $s3QueueEntity->getContent();
        $siteEntity = $s3QueueEntity->getSite();
        $thumbEntity = $s3QueueEntity->getThumStyle();
        $contentMediaEntity = $contentEntity->getMedia()->first();
        if (empty($contentMediaEntity)) {
            $s3QueueEntity->setStatus('Error');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        }
        if (!$this->mm()->isImage($contentEntity)) {
            $s3QueueEntity->setStatus('Error');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        }
        $cacheFilename = $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
        $scale = $s3QueueEntity->getThumbScale();
        $thumb = $this->getThumbService()->open($cacheFilename);
        $width = $thumbEntity->getWidth() * $scale;
        $height = $thumbEntity->getHeight() * $scale;
        switch ($thumbEntity->getMode()) {
            case 'resize':
                $thumb = $thumb->resize($width, $height);
                break;
            case 'scaleResize':
                $thumb = $thumb->scaleResize($width, $height);
                break;
            case 'forceResize':
                $thumb = $thumb->forceResize($width, $height);
                break;
            case 'cropResize':
                $thumb = $thumb->cropResize($width, $height);
                break;
            case 'zoomCrop':
                $thumb = $thumb->zoomCrop($width, $height);
                break;
        }

        $thumbCacheFile = $thumb->cacheFile('guess', $thumbEntity->getQuality(), true);

        $uploadDateTime = new \DateTime();
        $filename = $contentMediaEntity->getId() . '.' . $contentMediaEntity->getExtension();
        $md5string = md5($uploadDateTime->format('Y-m-d H:i:s') . '(' . $thumbEntity->getId() . ')(' . $scale . ')(' . $thumbEntity->getMode() . ')(' . $thumbEntity->getWidth() . ')(' . $thumbEntity->getHeight() . ')');
        $path = '/' . $siteEntity->getSlug() . '/thumb/' . $md5string . '/' . $filename;
        $s3Key = strtolower($this->pathPrefix . $path);

        $s3client = $this->s3();
        try {
            $result = $s3client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $s3Key,
                'SourceFile' => $thumbCacheFile,
                'CacheControl' => 'max-age=172800',
                "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years")),
                'ContentType' => $contentMediaEntity->getMime(),
                'ACL' => 'public-read'
            ]);

            $s3QueueEntity->setPrefix($this->pathPrefix);
            $s3QueueEntity->setPath($s3Key);
            $s3QueueEntity->setUploadedDate($uploadDateTime);
            $s3QueueEntity->setStatus('Done');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        } catch (\Aws\Exception\AwsException $e) {
            $s3QueueEntity->setStatus('Error');
            $this->em()->persist($s3QueueEntity);
            $this->em()->flush();
            return;
        }
    }

    private function sanitizeFilename($filename)
    {
        $mbStrLen = mb_strlen($filename, 'utf-8');
        $strLen = strlen($filename);
        if ($mbStrLen == $strLen) {
            $cleaned = strtolower($filename);
        } else {
            $cleaned = urlencode(mb_strtolower($filename));
        }
        $cleaned = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $cleaned);
        $cleaned = preg_replace("([\.]{2,})", '', $cleaned);
        $cleaned = preg_replace('/&.+?;/', '', $cleaned);
        $cleaned = preg_replace('/_/', '-', $cleaned);
        $cleaned = preg_replace('/\./', '-', $cleaned);
        $cleaned = preg_replace('/[^a-z0-9\s-.]/i', '', $cleaned);
        $cleaned = preg_replace('/\s+/', '-', $cleaned);
        $cleaned = preg_replace('|-+|', '-', $cleaned);
        $cleaned = trim($cleaned, '-');
        return $cleaned;
    }


    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }

    /**
     * @return mixed
     */
    public function getPathPrefix()
    {
        return $this->pathPrefix;
    }

    /**
     * @return mixed
     */
    public function getDomainURLPrefix()
    {
        return $this->domainURLPrefix;
    }

    /**
     * @return S3QueueRepository
     */
    public function getRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:S3QueueEntity');
    }


    /**
     * @return \Aws\S3\S3Client
     */
    public function s3()
    {
        return $this->container->get('aws.s3');
    }

}
