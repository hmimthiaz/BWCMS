<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Gregwar\ImageBundle\ImageHandler;


class ThumbService extends BaseService
{

    private $kernel = null;


    function __construct(KernelInterface $kernel = null, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->kernel = $kernel;
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function init()
    {
        if (!$this->loaded) {

        }
        $this->loaded = true;
    }

    /**
     * @return TemplateService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * @param $file
     * @return ImageHandler
     */
    public function open($file)
    {
        if (strlen($file)>=1 && $file[0] == '@') {
            $file = $this->kernel->locateResource($file);
        }

        return $this->createInstance($file);
    }

    /**
     * @param $w
     * @param $h
     * @return ImageHandler
     */
    public function create($w, $h)
    {
        return $this->createInstance(null, $w, $h);
    }

    /**
     * @param $file
     * @param null $w
     * @param null $h
     * @return ImageHandler
     */
    private function createInstance($file, $w = null, $h = null)
    {

        $container = $this->container;
        $rootDirectory = $this->getKernel()->getRootDir();
        $cacheDirectory = $rootDirectory . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->getKernel()->getEnvironment() . DIRECTORY_SEPARATOR . 'thumb';

        $image = new ImageHandler($file, $w, $h);
        $image->setCacheDir($cacheDirectory);
        $image->setCacheDirMode(0755);
        $image->setFileCallback(function($file) use ($container) {
            return $container->get('templating.helper.assets')->getUrl($file);
        });
        return $image;
    }


}
