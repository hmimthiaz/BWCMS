<?php

namespace Bellwether\BWCMSBundle\Classes\Base;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

class BWCMSBaseController extends Controller
{

    /**
     * @return User
     */
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }


    /**
     * @return BWCMSMedia
     */
    public function media(){
        return $this->container->get('BWCMS.Media');
    }

    public function dump($var, $maxDepth = 2, $stripTags = true){
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }


}
