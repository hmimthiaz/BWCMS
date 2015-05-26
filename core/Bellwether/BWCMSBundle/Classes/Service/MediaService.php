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
use Bellwether\BWCMSBundle\Entity\ThumbStyle;

class MediaService extends BaseService
{

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
            $this->fs = new Filesystem();
        }
        $this->loaded = true;
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


    function cloneMedia($fileToClone)
    {
        $sourceFile = $this->getFilePath($fileToClone, true);
        $sourceInfo = pathinfo($sourceFile);
        if (!isset($sourceInfo['extension'])) {
            $sourceInfo['extension'] = '';
        }

        $finalFileName = $sourceInfo['filename'] . ((!empty ($sourceInfo['extension'])) ? '.' : '') . $sourceInfo['extension'];
        $check = 1;
        while (file_exists($sourceInfo['dirname'] . DIRECTORY_SEPARATOR . $finalFileName)) {
            $finalFileName = $sourceInfo['filename'] . '_' . $check . ((!empty ($sourceInfo['extension'])) ? '.' : '') . $sourceInfo['extension'];
            $check++;
        }
        $this->fs->copy($sourceFile,$sourceInfo['dirname'] . DIRECTORY_SEPARATOR . $finalFileName);

        return $finalFileName;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return array
     */
    public function handleUpload(UploadedFile $uploadedFile)
    {
        $data = array();
        if (null !== $uploadedFile && $uploadedFile->isValid()) {

            $uploadFolder = $this->getUploadDir();
            if (!$this->fs->exists($uploadFolder)) {
                $this->fs->mkdir($uploadFolder);
            }
            $filename = $this->generateFileName($uploadedFile);
            $uploadedFile->move($uploadFolder, $filename);

            $data['originalName'] = $uploadedFile->getClientOriginalName();
            $data['mimeType'] = $uploadedFile->getClientMimeType();
            $data['size'] = $uploadedFile->getClientSize();
            $data['extension'] = $uploadedFile->getClientOriginalExtension();
            if (empty($data['extension'])) {
                $data['extension'] = $uploadedFile->guessClientExtension();
            }
            $data['filename'] = $filename;
            $data['width'] = 0;
            $data['height'] = 0;
            if ($this->isImage($uploadedFile->getClientOriginalExtension(), $uploadedFile->getClientMimeType())) {
                $imageInfo = getimagesize($uploadFolder . DIRECTORY_SEPARATOR . $filename);
                if (!empty($imageInfo)) {
                    $data['width'] = $imageInfo[0];
                    $data['height'] = $imageInfo[1];
                }
            }
        }
        return $data;
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
        if (empty($contentEntity->getFile())) {
            return null;
        }
        if ($this->isImage($contentEntity->getFile(), $contentEntity->getMime())) {
            $filename = $this->getFilePath($contentEntity->getFile());
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

    public function getSystemThumbURL($filename, $mime, $extension, $width, $height)
    {
        if ($this->isImage($filename, $mime)) {
            $publicFilename = $this->getFilePath($filename);
            $thumbURL = $this->getThumbService()->open($publicFilename)->resize($width, $height)->cacheFile('guess');
        } else {
            $thumbURL = $this->getThumbService()->open($this->getMimeResourceImage($extension))->resize($width, $height)->cacheFile('guess');
        }
        return $thumbURL;
    }

    /**
     * @param $filename
     * @param $mime
     * @return bool
     */
    public function isImage($filename, $mime)
    {
        $ext = preg_match('/\.([^.]+)$/', $filename, $matches) ? strtolower($matches[1]) : false;
        $imageExtension = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
        if ('image/' == substr($mime, 0, 6) || in_array($ext, $imageExtension)) {
            return true;
        }
        return false;
    }

    public function deleteMedia($filename)
    {
        $filePath = $this->getFilePath($filename, true);
        try {
            $this->fs->remove($filePath);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    private function getMimeResourceImage($extension)
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


    private function generateFileName(UploadedFile $file)
    {
        $filename = $this->sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();

        $currentYear = ( string )gmdate('Y', time());
        $currentMonth = ( string )gmdate('m', time());
        $currentDay = ( string )gmdate('d', time());
        $filenameWithDate = "{$currentYear}{$currentMonth}{$currentDay}_{$filename}";

        $uploadFolder = $this->getUploadDir();

        $finalFileName = $filenameWithDate . ((!empty ($extension)) ? '.' : '') . $extension;
        $check = 1;
        while (file_exists($uploadFolder . $finalFileName)) {
            $finalFileName = $filenameWithDate . '_' . $check . ((!empty ($extension)) ? '.' : '') . $extension;
            $check++;
        }

        return $finalFileName;
    }


    private function getUploadDir()
    {
        $uploadFolder = $this->uploadFolder;
        $currentYear = ( string )gmdate('Y', time());
        $currentMonth = ( string )gmdate('m', time());
        $currentDay = ( string )gmdate('d', time());
        return $uploadFolder . DIRECTORY_SEPARATOR . $currentYear . DIRECTORY_SEPARATOR . $currentMonth . DIRECTORY_SEPARATOR . $currentDay . DIRECTORY_SEPARATOR;
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