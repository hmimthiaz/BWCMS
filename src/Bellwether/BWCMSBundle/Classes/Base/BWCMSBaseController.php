<?php

namespace Bellwether\BWCMSBundle\Classes\Base;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Classes\BWCMSSite;
use Bellwether\BWCMSBundle\Classes\BWCMSContent;
use Bellwether\BWCMSBundle\Classes\BWCMSMedia;


class BWCMSBaseController extends Controller
{

    /**
     * @return UserEntity
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return UserEntity
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
     * @return BWCMSSite
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site');
    }

    /**
     * @return BWCMSContent
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content');
    }

    /**
     * @return BWCMSMedia
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media');
    }


    public function dump($var, $maxDepth = 2, $stripTags = true)
    {
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }


}
