<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Gregwar\Image\Image;

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
        $this->initMedia();
    }

    /**
     * @return MediaService
     */
    public function getManager()
    {
        return $this;
    }

    private function initMedia()
    {
        $rootDirectory = $this->getKernel()->getRootDir();
        $webRoot = realpath($rootDirectory . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');
        $this->webPath = $this->container->getParameter('media.path');
        $this->uploadFolder = $webRoot . DIRECTORY_SEPARATOR . $this->webPath;
        $this->fs = new Filesystem();
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