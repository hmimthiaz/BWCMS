<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;

class SiteManager extends BaseService
{

    private $currentSite = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SiteManager
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * @return SiteEntity
     */
    public function getCurrentSite()
    {
        if ($this->currentSite == null) {
            $criteria = array(
                'isDefault' => true
            );
            $this->currentSite = $this->em()->getRepository('BWCMSBundle:SiteEntity')->findOneBy($criteria);
            if($this->currentSite == null){
                $siteEntity = new SiteEntity();
                $siteEntity->setName('Default');
                $siteEntity->setLocale('en');
                $siteEntity->setDirection('ltr');
                $siteEntity->setSlug('en');
                $siteEntity->setDomain($this->getRequest()->getHost());
                $siteEntity->setIsDefault(true);
                $this->em()->persist($siteEntity);
                $this->em()->flush();
                $this->currentSite = $siteEntity;
            }
        }
        return $this->currentSite;
    }

}