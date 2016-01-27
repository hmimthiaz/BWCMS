<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;


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


}
