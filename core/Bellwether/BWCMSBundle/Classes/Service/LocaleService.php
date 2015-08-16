<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\LocaleEntity;
use Bellwether\BWCMSBundle\Entity\LocaleEntityRepository;
use Bellwether\BWCMSBundle\Entity\SiteEntity;


class LocaleService extends BaseService
{

    /**
     * @var SiteEntity
     */
    private $curentSite;

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

    public function add($string)
    {
        $localeEntity = new LocaleEntity();
        $localeEntity->setHash(md5($string));
        $localeEntity->setValue($string);
        $localeEntity->setSite($this->sm()->getCurrentSite());
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

    public function save($stringHash, $value)
    {


    }

    /**
     * @return LocaleEntityRepository
     */
    public function getRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:LocaleEntity');
    }


}
