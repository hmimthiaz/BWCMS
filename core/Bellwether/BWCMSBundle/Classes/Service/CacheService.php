<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Doctrine\Common\Cache\FilesystemCache;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class CacheService extends BaseService
{

    private $currentSite;

    /**
     * @var FilesystemCache
     */
    private $objectCache;

    /**
     * @var FilesystemCache
     */
    private $pageCache;

    /**
     * @var bool
     */
    private $cacheCurrentPage = false;

    /**
     * @var int
     */
    private $cachePageLifetime = 600;

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

            $objectCacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'objectCache';
            $this->objectCache = new FilesystemCache($objectCacheDir, '.BWObjCache.bin');
            $this->objectCache->setNamespace('BWCMS');

            $pageCacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . 'pageCache';
            $this->pageCache = new FilesystemCache($pageCacheDir, '.BWPageCache.bin');
            $this->pageCache->setNamespace('BWCMS');

        }
        $this->loaded = true;
    }

    public function checkPageCacheResponse(GetResponseEvent $event)
    {
        $env = $this->container->get('kernel')->getEnvironment();
        if ($env != 'prod') {
            return;
        }
        if ($this->isUserLoggedIn()) {
            return;
        }
        $baseURL = $event->getRequest()->getBaseUrl();
        $pathInfo = $event->getRequest()->getPathInfo();
        $pageCacheHash = md5($baseURL . $pathInfo);
        /**
         * @var Response $pageResponse
         */
        $pageResponse = $this->pageCache->fetch($pageCacheHash);
        if ($pageResponse !== false) {
            $pageContent = $pageResponse->getContent();
            $pageContent .= "\n<!-- Page Cached -->";
            $pageResponse->setContent($pageContent);
            $event->setResponse($pageResponse);
        }
    }

    public function savePageCacheReponse(FilterResponseEvent $event)
    {
        $env = $this->container->get('kernel')->getEnvironment();
        if ($env != 'prod') {
            return;
        }
        if ($this->isUserLoggedIn()) {
            return;
        }
        if ($this->cacheCurrentPage) {
            $baseURL = $event->getRequest()->getBaseUrl();
            $pathInfo = $event->getRequest()->getPathInfo();
            $pageCacheHash = md5($baseURL . $pathInfo);
            $this->pageCache->save($pageCacheHash, $event->getResponse(), $this->cachePageLifetime);
            $this->cacheCurrentPage = false;
        }
    }

    /**
     * @return bool
     */
    public function isUserLoggedIn()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return true;
        }
        return false;
    }

    /**
     * @param SiteEntity $site
     */
    public function setCurrentSite($site = null)
    {
        $this->currentSite = $site;
        if (is_null($site)) {
            $this->objectCache->setNamespace('BWCMS');
        } else {
            $this->objectCache->setNamespace($this->currentSite->getId());
        }
    }

    /**
     * @param $id
     * @return bool|mixed|string
     */
    public function fetch($id)
    {
        return $this->objectCache->fetch($id);
    }

    /**
     * @param array $keys
     * @return array|\mixed[]
     */
    public function fetchMultiple(array $keys)
    {
        return $this->objectCache->fetchMultiple($keys);
    }

    /**
     * @param $id
     * @param $data
     * @param int $lifeTime
     * @return bool
     */
    public function  save($id, $data, $lifeTime = 0)
    {
        return $this->objectCache->save($id, $data, $lifeTime);
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->objectCache->delete($id);
    }

    /**
     * @return boolean
     */
    public function isCacheCurrentPage()
    {
        return $this->cacheCurrentPage;
    }

    /**
     * @param boolean $cacheCurrentPage
     */
    public function setCacheCurrentPage($cacheCurrentPage)
    {
        $this->cacheCurrentPage = $cacheCurrentPage;
    }

    /**
     * @return int
     */
    public function getCachePageLifetime()
    {
        return $this->cachePageLifetime;
    }

    /**
     * @param int $cachePageLifetime
     */
    public function setCachePageLifetime($cachePageLifetime)
    {
        $this->cachePageLifetime = $cachePageLifetime;
    }


}
