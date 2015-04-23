<?php

namespace Bellwether\BWCMSBundle\Classes\Base;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;
use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Classes\Service\SiteService;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;
use Bellwether\BWCMSBundle\Classes\Service\MailService;
use Bellwether\BWCMSBundle\Classes\Service\PreferenceService;
use Bellwether\BWCMSBundle\Classes\Service\TemplateService;
use Symfony\Component\HttpFoundation\Session\Session;


abstract class BaseService  extends ContainerAware
{

    private $path;

    /**
     * @return string
     */
    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }
        return $this->path;
    }

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
     * @return SiteService
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site')->getManager();
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }

    /**
     * @return PreferenceService
     */
    public function pref()
    {
        return $this->container->get('BWCMS.Preference')->getManager();
    }

    /**
     * @return TemplateService
     */
    public function tp()
    {
        return $this->container->get('BWCMS.Template')->getManager();
    }

    /**
     * @return Session
     */
    public function session()
    {
        return $this->container->get('session');
    }

    /**
     * @return MailService
     */
    public function mailer()
    {
        return $this->container->get('BWCMS.Mailer');
    }

    /**
     * @return SecurityContext
     */
    public function getSecurityContext(){
        return $this->container->get('security.context');
    }

    /**
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function getEventDispatcher(){
        return $this->container->get('event_dispatcher');
    }

    /**
     * @return \JMS\Serializer\Serializer
     */
    public function getSerializer(){
        return $this->container->get('serializer');
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
