<?php

namespace Bellwether\BWCMSBundle\Classes\Base;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Bellwether\BWCMSBundle\Entity\UserEntity;


class BaseService  extends ContainerAware
{

    /**
     * @var RequestStack
     *
     * @api
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return Request|null
     */
    public function getRequest(){
        return $this->requestStack->getCurrentRequest();
    }

    public function getKernel(){
        return $this->container->get( 'kernel' );
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return SecurityContext
     */
    public function getSecurityContext(){
        return $this->container->get('security.context');
    }

    /**
     * @return UserEntity
     */
    public function getUser(){
        return $this->getSecurityContext()->getToken()->getUser();
    }


    public function dump($var, $maxDepth = 2, $stripTags = true){
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }

}
