<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class BWCMSMedia extends BWCMSBaseService
{

    private $uploadFolder;
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

    private function initMedia()
    {
        $rootDirectory = $this->getKernel()->getRootDir();
        $webRoot = realpath($rootDirectory . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'web');
        $this->uploadFolder = $webRoot . DIRECTORY_SEPARATOR . $this->container->getParameter('media.path');

        $this->fs = new Filesystem();
    }


    public function handleUpload()
    {
        /**
         * @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
         */
        $uploadedFile = $this->getRequest()->files->get('file');
        if (null !== $uploadedFile && $uploadedFile->isValid()) {
            $uploadFolder = $this->getUploadDir();
            if(!$this->fs->exists($uploadFolder)){
                $this->fs->mkdir($uploadFolder);
            }
            $filename = $this->generateFileName($uploadedFile);
            $uploadedFile->move($uploadFolder,$filename);
        }
    }


    private function generateFileName(UploadedFile $file)
    {
        $filename = $this->sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension =  $file->getClientOriginalExtension();

        $currentYear = ( string )gmdate('Y', time());
        $currentMonth = ( string )gmdate('m', time());
        $currentDay = ( string )gmdate('d', time());
        $filenameWithDate = "{$currentYear}{$currentMonth}{$currentDay}_{$filename}";

        $uploadFolder = $this->getUploadDir();

        $finalFileName =  $filenameWithDate . ((! empty ( $extension ) ) ? '.' : '') . $extension;
        $check = 1;
        while ( file_exists ( $uploadFolder . $finalFileName ) ) {
            $finalFileName = $filenameWithDate . '_' . $check  . ((! empty ( $extension ) ) ? '.' : '') . $extension;
            $check ++;
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