<?php

namespace Bellwether\BWCMSBundle\Classes\Base;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Classes\SiteManager;
use Bellwether\BWCMSBundle\Classes\ContentManager;
use Bellwether\BWCMSBundle\Classes\MediaManager;


class BaseController extends Controller
{

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return SiteEntity
     */
    public function getSite()
    {
        return $this->sm()->getCurrentSite();
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return SiteManager
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site')->getManager();
    }

    /**
     * @return ContentManager
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return MediaManager
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }


    public function dump($var, $maxDepth = 2, $stripTags = true)
    {
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }


}
