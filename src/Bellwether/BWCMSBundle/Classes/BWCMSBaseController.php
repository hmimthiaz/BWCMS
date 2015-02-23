<?php

namespace Bellwether\BWCMSBundle\Classes;


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

}
