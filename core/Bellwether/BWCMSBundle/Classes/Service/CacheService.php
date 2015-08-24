<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Doctrine\Common\Cache\FilesystemCache;

class CacheService extends BaseService
{

    private $currentSite;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return CacheService
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
            $this->getFileCache()->setNamespace('BWCMS');
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
            $this->getFileCache()->setNamespace('BWCMS');
        } else {
            $this->getFileCache()->setNamespace($this->currentSite->getId());
        }
    }

    /**
     * @param $id
     * @return bool|mixed|string
     */
    public function fetch($id)
    {
        return $this->getFileCache()->fetch($id);
    }

    /**
     * @param array $keys
     * @return array|\mixed[]
     */
    public function fetchMultiple(array $keys)
    {
        return $this->getFileCache()->fetchMultiple($keys);
    }

    /**
     * @param $id
     * @param $data
     * @param int $lifeTime
     * @return bool
     */
    public function  save($id, $data, $lifeTime = 0)
    {
        return $this->getFileCache()->save($id, $data, $lifeTime);
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->getFileCache()->delete($id);
    }

    /**
     * @return FilesystemCache
     */
    public function getFileCache()
    {
        return $this->container->get('BWCMS.DoctrineFileCache');

    }

}
