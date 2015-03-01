<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Gregwar\Image\Image;

class MediaManager extends BaseService
{

    private $uploadFolder;
    private $webPath;
    private $mimeIconsExtension = null;
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
     * @return MediaManager
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
     * @return array
     */
    public function handleUpload()
    {
        $data = array();
        /**
         * @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
         */
        $uploadedFile = $this->getRequest()->files->get('file');
        if (null !== $uploadedFile && $uploadedFile->isValid()) {
            $this->dump($uploadedFile);

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
        }
        return $data;
    }

    public function getThumbURL($filename, $mime, $extension , $width, $height)
    {
        if ($this->isImage($filename, $mime)) {
            $publicFilename = $this->getFilePublicPath($filename);
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

    private function getMimeResourceImage($extension)
    {
        if (in_array($extension, $this->getMimeIconsExtensions())) {
            return '@BWCMSBundle/Resources/mime/'.$extension.'.png';
        }
        return '@BWCMSBundle/Resources/mime/unknown.png';
    }

    private function getMimeIconsExtensions()
    {
        if ($this->mimeIconsExtension == null) {
            $this->mimeIconsExtension = array();
            /**
             * @var \Symfony\Component\HttpKernel\Config\FileLocator $fileLocator
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */
            $fileLocator = $this->container->get('file_locator');
            $mimeLocation = $fileLocator->locate('@BWCMSBundle/Resources/mime');
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in($mimeLocation);
            foreach ($finder as $file) {
                $this->mimeIconsExtension[] = $file->getBasename('.' . $file->getExtension());
            }
        }
        return $this->mimeIconsExtension;
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
    public function getFilePublicPath($filename)
    {
        if (empty ($filename)) {
            return false;
        }
        if (preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})_/", $filename, $regs)) {
            $publicPath = $this->webPath . DIRECTORY_SEPARATOR .
                $regs [1] . DIRECTORY_SEPARATOR .
                $regs [2] . DIRECTORY_SEPARATOR .
                $regs [3] . DIRECTORY_SEPARATOR .
                $filename;
            return $publicPath;
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