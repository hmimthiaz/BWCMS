<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\SiteRepository;

class SiteService extends BaseService
{

    private $defaultSite = null;

    private $currentSite = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SiteService
     */
    public function getManager()
    {
        return $this;
    }

    public function setCurrentSite($siteId = null)
    {
        $this->currentSite = null;
        $this->session()->remove('currentSiteId');
        if (!is_null($siteId)) {
            $this->session()->set('currentSiteId', $siteId);
        }
    }

    /**
     * @return SiteEntity
     */
    public function getCurrentSite()
    {
        if ($this->currentSite == null) {
            $currentSiteId = $this->session()->get('currentSiteId', null);
            if ($currentSiteId != null) {
                $this->currentSite = $this->getSiteRepository()->find($currentSiteId);
            }
            if ($this->currentSite == null) {
                $this->currentSite = $this->getDefaultSite();
            }
            $this->session()->set('currentSiteId', $this->currentSite->getId());
        }
        return $this->currentSite;
    }

    /**
     * @return Array();
     */
    public function getAllSites()
    {
        return $this->getSiteRepository()->findAll();
    }

    public function getSiteBySlug($slug)
    {
        $criteria = array(
            'slug' => $slug
        );
        return $this->getSiteRepository()->findOneBy($criteria);
    }

    /**
     * @return SiteEntity
     */
    public function getDefaultSite()
    {
        if ($this->defaultSite == null) {
            $criteria = array(
                'isDefault' => true
            );
            $this->defaultSite = $this->getSiteRepository()->findOneBy($criteria);
            if ($this->defaultSite == null) {
                $siteEntity = new SiteEntity();
                $siteEntity->setName('Default');
                $siteEntity->setLocale('en');
                $siteEntity->setDirection('ltr');
                $siteEntity->setSlug('en');
                $siteEntity->setDomain($this->getRequest()->getHost());
                $siteEntity->setIsDefault(true);
                $this->em()->persist($siteEntity);
                $this->em()->flush();
                $this->defaultSite = $siteEntity;
            }
        }
        return $this->defaultSite;
    }

    /**
     * @return SiteRepository
     */
    public function getSiteRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:SiteEntity');
    }

}