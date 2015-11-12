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
use Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;

class MediaService extends BaseService
{

    private $mediaFolder;
    private $uploadFolder;
    private $webRoot;
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
            $this->webRoot = realpath($rootDirectory . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');

            $this->webPath = $this->container->getParameter('media.path');
            $this->uploadFolder = $this->webRoot . DIRECTORY_SEPARATOR . $this->webPath;
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
     * @param $mediaEntity
     * @return string|null
     */
    public function getMediaCachePath($mediaEntity)
    {
        $folderHash = md5($mediaEntity->getId());
        $folderName = '';
        for ($index = 0; $index <= 10; $index++) {
            $folderName .= substr($folderHash, $index, 1) . DIRECTORY_SEPARATOR;
        }
        $path = $this->mediaFolder . DIRECTORY_SEPARATOR . $folderName;
        $path .= $mediaEntity->getId() . '.bin';
        return $path;
    }

    /**
     * @param $mediaEntity
     */
    public function checkAndCreateMediaCacheFile($mediaEntity)
    {
        $cacheFile = $this->getMediaCachePath($mediaEntity);
        if (!file_exists($cacheFile)) {
            $this->fs->mkdir(dirname($cacheFile));
            $localFile = fopen($cacheFile, 'wb');
            fwrite($localFile, stream_get_contents($mediaEntity->getData()));
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

        return $this->generateUrl('_bwcms_admin_content_thumb', array(
            'contentId' => $contentEntity->getId(),
            'width' => $width,
            'height' => $height,
        ));
    }


    /**
     * @param $thumbSlug
     * @param null $site
     * @return null|object
     */
    public function getThumbStyle($thumbSlug, $site = null)
    {
        $repo = $this->em()->getRepository('BWCMSBundle:ThumbStyleEntity');
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
     * @param ThumbStyleEntity $thumbStyle
     */
    public function getContentThumbURLWithStyle($contentEntity, $thumbStyle, $scaleFactor = 1.0)
    {
        $filename = $this->getContentFile($contentEntity);
        if (empty($filename)) {
            return null;
        }
        $thumb = $this->getThumbService()->open($filename);
        $width = $thumbStyle->getWidth() * $scaleFactor;
        $height = $thumbStyle->getHeight() * $scaleFactor;
        switch ($thumbStyle->getMode()) {
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

    function compress_png($pngFilePath, $max_quality = 90)
    {
        if (!function_exists('shell_exec')) {
            return null;
        }
        if (!file_exists($pngFilePath)) {
            return null;
        }

        $min_quality = 60;
        // '-' makes it use stdout, required to save to $compressed_png_content variable
        // '<' makes it read from the given file path
        // escapeshellarg() makes this safe to use with any path
        $compressed_png_content = shell_exec("pngquant --quality=$min_quality-$max_quality - < " . escapeshellarg($pngFilePath));

        if (!$compressed_png_content) {
            return null;
        }
        return $compressed_png_content;
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

    /**
     * @return mixed
     */
    public function getMediaFolder()
    {
        return $this->mediaFolder;
    }

    /**
     * @return mixed
     */
    public function getWebRoot()
    {
        return $this->webRoot;
    }


}
