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

    private $adminCurrentSite = null;

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

    /**
     * @param SiteEntity $site
     */
    public function setCurrentSite($site = null)
    {
        $this->currentSite = $site;
    }

    /**
     * @return null|SiteEntity
     */
    public function getCurrentSite()
    {
        return $this->currentSite;
    }

    public function setAdminCurrentSite($siteId = null)
    {
        $this->adminCurrentSite = null;
        $this->session()->remove('adminCurrentSiteId');
        if (!is_null($siteId)) {
            $this->session()->set('adminCurrentSiteId', $siteId);
        }
    }

    /**
     * @return SiteEntity
     */
    public function getAdminCurrentSite()
    {
        if ($this->adminCurrentSite == null) {
            $currentSiteId = $this->session()->get('adminCurrentSiteId', null);
            if ($currentSiteId != null) {
                $this->adminCurrentSite = $this->getSiteRepository()->find($currentSiteId);
            }
            if ($this->adminCurrentSite == null) {
                $this->adminCurrentSite = $this->getDefaultSite();
            }
            $this->session()->set('adminCurrentSiteId', $this->adminCurrentSite->getId());
        }
        return $this->adminCurrentSite;
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
                $siteEntity->setSkinFolderName('Generic');
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
