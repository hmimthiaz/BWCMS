<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseService;

use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;

class BWCMSSite extends BWCMSBaseService
{

    private $currentSite;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);


    }

    private function initSite(){
//        $this->em()->find()


    }


    /**
     * @return SiteEntity
     */
    public function getCurrentSite()
    {
        return $this->currentSite;
    }

}