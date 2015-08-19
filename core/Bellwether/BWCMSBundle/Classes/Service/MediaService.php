<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Gregwar\Image\Image;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;
use Bellwether\BWCMSBundle\Entity\ThumbStyle;

class MediaService extends BaseService
{

    private $mediaFolder;
    private $uploadFolder;
    private $webPath;
    private $extensionMimeIcons = null;
    /**
     * @var \Symfony\Component\Filesystem\Filesystem $fs
     */
    private $fs;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }


    /**
     * @return MediaService
     */
    public function getManager()
    {
        return $this;
    }

    public function init()
    {
        if (!$this->loaded) {
            $rootDirectory = $this->getKernel()->getRootDir();
            $webRoot = realpath($rootDirectory . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');

            $this->webPath = $this->container->getParameter('media.path');
            $this->uploadFolder = $webRoot . DIRECTORY_SEPARATOR . $this->webPath;
            $this->mediaFolder = $rootDirectory . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->getKernel()->getEnvironment() . DIRECTORY_SEPARATOR . 'media';
            $this->fs = new Filesystem();
        }
        $this->loaded = true;
    }


    /**
     * @param ContentEntity $contentEntity
     * @return bool
     */
    public function isMedia(ContentEntity $contentEntity)
    {
        if ($contentEntity->getMedia()->count() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param ContentEntity $contentEntity
     * @return bool
     */
    public function isImage(ContentEntity $contentEntity)
    {
        /**
         * @var ContentMediaEntity $media
         */
        if (!$this->isMedia($contentEntity)) {
            return false;
        }
        $media = $contentEntity->getMedia()->first();
        $imageExtension = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
        if ('image/' == substr($media->getMime(), 0, 6) || in_array($media->getExtension(), $imageExtension)) {
            return true;
        }
        return false;
    }

    /**
     * @param ContentMediaEntity $contentMediaEntity
     * @return string|null
     */
    public function getMediaCachePath($contentMediaEntity)
    {
        $folderHash = md5($contentMediaEntity->getId());
        $folderName = substr($folderHash, 0, 1) . DIRECTORY_SEPARATOR .
            substr($folderHash, 1, 1) . DIRECTORY_SEPARATOR .
            substr($folderHash, 2, 1) . DIRECTORY_SEPARATOR .
            substr($folderHash, 3, 1);
        $path = $this->mediaFolder . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;
        $path .= $contentMediaEntity->getId() . '.bin';
        return $path;
    }

    /**
     * @param ContentMediaEntity $contentMediaEntity
     */
    public function checkAndCreateMediaCacheFile($contentMediaEntity)
    {
        $cacheFile = $this->getMediaCachePath($contentMediaEntity);
        if (!file_exists($cacheFile)) {
            $this->fs->mkdir(dirname($cacheFile));
            $localFile = fopen($cacheFile, 'wb');
            fwrite($localFile, stream_get_contents($contentMediaEntity->getData()));
            fclose($localFile);
        }
        return $cacheFile;
    }


    /**
     * @param ContentEntity $contentEntity
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function getContentThumbURL($contentEntity, $width = 128, $height = 128)
    {
        $contentClass = $this->cm()->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        if (!$contentClass->isUploadEnabled()) {
            $thumbURL = $this->getThumbService()->open($contentClass->getImage())->resize($width, $height)->cacheFile('guess');
            return $thumbURL;
        }
        if (!$this->isMedia($contentEntity)) {
            return null;
        }
        $media = $contentEntity->getMedia()->first();
        $this->checkAndCreateMediaCacheFile($media);
        if ($this->isImage($contentEntity)) {
            $thumbURL = $this->getThumbService()->open($this->getMediaCachePath($media))->resize($width, $height)->cacheFile('guess');
        } else {
            $thumbURL = $this->getThumbService()->open($this->getMimeResourceImage($media->getMime()))->resize($width, $height)->cacheFile('guess');
        }
        return $thumbURL;
    }


    /**
     * @param $thumbSlug
     * @param null $site
     * @return null|object
     */
    public function getThumbStyle($thumbSlug, $site = null)
    {
        $repo = $this->em()->getRepository('BWCMSBundle:ThumbStyle');
        $criteria = array(
            'slug' => $thumbSlug,
            'site' => null
        );
        if (!empty($site)) {
            $criteria['site'] = $site;
        }
        return $repo->findOneBy($criteria);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return array
     */
    public function handleUpload(UploadedFile $uploadedFile)
    {
        $data = array();
        if (null !== $uploadedFile && $uploadedFile->isValid()) {
            $uploadedTempFile = $uploadedFile->getPathname();
            $data['originalName'] = $uploadedFile->getClientOriginalName();
            $data['filename'] = $this->sanitizeFilename($uploadedFile->getClientOriginalName());
            $data['mimeType'] = $uploadedFile->getClientMimeType();
            $data['size'] = $uploadedFile->getClientSize();
            $data['extension'] = $uploadedFile->getClientOriginalExtension();
            if (empty($data['extension'])) {
                $data['extension'] = $uploadedFile->guessClientExtension();
            }
            $data['width'] = 0;
            $data['height'] = 0;
            if ($this->checkIfImage($uploadedFile->getClientOriginalExtension(), $uploadedFile->getClientMimeType())) {
                $imageInfo = getimagesize($uploadedTempFile);
                if (!empty($imageInfo)) {
                    $data['width'] = $imageInfo[0];
                    $data['height'] = $imageInfo[1];
                }
            }
            $data['binary'] = null;
            if (file_exists($uploadedTempFile)) {
                $mediaStream = fopen($uploadedTempFile, 'rb');
                $data['binary'] = stream_get_contents($mediaStream);
                fclose($mediaStream);
            }
        }
        return $data;
    }

    /**
     * @param $filename
     * @param $mime
     * @return bool
     */
    private function checkIfImage($filename, $mime)
    {
        $ext = preg_match('/\.([^.]+)$/', $filename, $matches) ? strtolower($matches[1]) : false;
        $imageExtension = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
        if ('image/' == substr($mime, 0, 6) || in_array($ext, $imageExtension)) {
            return true;
        }
        return false;
    }


    /**
     * @param ContentEntity $contentEntity
     * @return string
     */
    public function getContentFile($contentEntity)
    {
        if (empty($contentEntity)) {
            return null;
        }
        if (!$this->isMedia($contentEntity)) {
            return null;
        }
        $media = $contentEntity->getMedia()->first();
        $this->checkAndCreateMediaCacheFile($media);
        if ($this->isImage($contentEntity)) {
            $filename = $this->getMediaCachePath($media);
        } else {
            $filename = $this->getMimeResourceImage($contentEntity->getExtension());
        }
        return $filename;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param ThumbStyle $thumbStyle
     */
    public function getContentThumbURLWithStyle($contentEntity, $thumbStyle)
    {
        $filename = $this->getContentFile($contentEntity);
        if (empty($filename)) {
            return null;
        }
        $thumb = $this->getThumbService()->open($filename);
        switch ($thumbStyle->getMode()) {
            case 'resize':
                $thumb = $thumb->resize($thumbStyle->getWidth(), $thumbStyle->getHeight());
                break;
            case 'scaleResize':
                $thumb = $thumb->scaleResize($thumbStyle->getWidth(), $thumbStyle->getHeight());
                break;
            case 'forceResize':
                $thumb = $thumb->forceResize($thumbStyle->getWidth(), $thumbStyle->getHeight());
                break;
            case 'cropResize':
                $thumb = $thumb->cropResize($thumbStyle->getWidth(), $thumbStyle->getHeight());
                break;
            case 'zoomCrop':
                $thumb = $thumb->zoomCrop($thumbStyle->getWidth(), $thumbStyle->getHeight());
                break;
        }
        return $thumb->cacheFile('guess');
    }

    /**
     * @deprecated
     * @param $filename
     * @return bool
     */
    public function deleteMedia($filename)
    {
        return true;
    }

    public function getMimeResourceImage($extension)
    {
        if (in_array($extension, $this->getMimeIconsExtensions())) {
            return '@BWCMSBundle/Resources/icons/mime/' . $extension . '.png';
        }
        return '@BWCMSBundle/Resources/icons/mime/unknown.png';
    }

    private function getMimeIconsExtensions()
    {
        if ($this->extensionMimeIcons == null) {
            $this->extensionMimeIcons = array();
            /**
             * @var \Symfony\Component\HttpKernel\Config\FileLocator $fileLocator
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */
            $fileLocator = $this->container->get('file_locator');
            $mimeLocation = $fileLocator->locate('@BWCMSBundle/Resources/icons/mime');
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in($mimeLocation);
            foreach ($finder as $file) {
                $this->extensionMimeIcons[] = $file->getBasename('.' . $file->getExtension());
            }
        }
        return $this->extensionMimeIcons;
    }


    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
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

}
