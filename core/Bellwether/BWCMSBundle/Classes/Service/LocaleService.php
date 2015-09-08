<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\LocaleEntity;
use Bellwether\BWCMSBundle\Entity\LocaleRepository;
use Bellwether\BWCMSBundle\Entity\SiteEntity;


class LocaleService extends BaseService
{

    /**
     * @var SiteEntity
     */
    private $currentSite = null;

    private $currentSiteId = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return LocaleService
     */
    public function getManager()
    {
        return $this;
    }

    public function init()
    {
        if (!$this->loaded) {

        }
        $this->loaded = true;
    }

    /**
     * @param SiteEntity $site
     */
    public function setCurrentSite($site = null)
    {
        $this->currentSite = $site;
        if (is_null($site)) {
            $this->currentSiteId = null;
        } else {
            $this->currentSiteId = $this->currentSite->getId();
        }
    }

    /**
     * @param string $string
     * @return string
     */
    public function get($string)
    {
        $cacheHash = $this->getCacheHash($string);
        $stringValue = $this->cache()->fetch($cacheHash);
        if ($stringValue === false) {
            $stringValue = $this->fetch($string);
            if (is_null($stringValue)) {
                $stringValue = $this->add($string);
            }
            $this->cache()->save($cacheHash, $stringValue);
        }

        if (func_num_args() == 1) {
            return $stringValue;
        }
        $parameters = array_slice(func_get_args(), 1);
        return vsprintf($stringValue, $parameters);
    }


    public function add($string)
    {
        $localeEntity = new LocaleEntity();
        $localeEntity->setHash(md5($string));
        $localeEntity->setText($string);
        $localeEntity->setValue($string);
        if ($this->admin()->isAdmin()) {
            $localeEntity->setSite($this->sm()->getAdminCurrentSite());
        } else {
            $localeEntity->setSite($this->sm()->getCurrentSite());
        }

        $this->em()->persist($localeEntity);
        $this->em()->flush();
        return $string;
    }

    public function fetch($string, $returnEntity = false)
    {
        $searchParameters = array();
        $searchParameters['hash'] = md5($string);
        $searchParameters['site'] = $this->sm()->getCurrentSite();
        $localeEntity = $this->getRepository()->findOneBy($searchParameters);
        if (is_null($localeEntity)) {
            return null;
        }
        if ($returnEntity) {
            return $localeEntity;
        }
        return $localeEntity->getValue();
    }

    public function save($id, $value)
    {
        $localeEntity = $this->getRepository()->find($id);
        if (!is_null($localeEntity)) {
            $localeEntity->setValue($value);
            $this->em()->persist($localeEntity);
            $this->em()->flush();
            $this->cache()->delete($this->getCacheHash($localeEntity->getText()));
        }
        return true;
    }

    public function getCacheHash($string)
    {
        return 'LCString_' . md5($string);
    }

    /**
     * @return LocaleRepository
     */
    public function getRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:LocaleEntity');
    }


}
