<?php

namespace Bellwether\BWCMSBundle\Classes\Base;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Classes\Service\AdminService;
use Bellwether\BWCMSBundle\Classes\Service\ACLService;
use Bellwether\BWCMSBundle\Classes\Service\CacheService;
use Bellwether\BWCMSBundle\Classes\Service\SiteService;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\ContentQueryService;
use Bellwether\BWCMSBundle\Classes\Service\SearchService;
use Bellwether\BWCMSBundle\Classes\Service\LocaleService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;
use Bellwether\BWCMSBundle\Classes\Service\MailService;
use Bellwether\BWCMSBundle\Classes\Service\PreferenceService;
use Bellwether\BWCMSBundle\Classes\Service\TemplateService;
use Bellwether\BWCMSBundle\Classes\Service\ThumbService;
use Bellwether\BWCMSBundle\Classes\Service\S3Service;

use Bellwether\BWCMSBundle\Entity\ContentEntity;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Gregwar\Image\Image;


abstract class BaseController extends Controller
{

    const NotifySuccess = 'Notify.Success';

    const NotifyInfo = 'Notify.Info';

    const NotifyWarning = 'Notify.Warning';

    const NotifyDanger = 'Notify.Danger';

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
     * @return string
     */
    public function getTemplateName($templateFilename)
    {
        return $this->tp()->getCurrentSkin()->getTemplateName($templateFilename);
    }

    /**
     * @param ContentEntity $contentEntity
     * @return string
     */
    public function getContentTemplate($contentEntity)
    {
        return $this->tp()->getCurrentSkin()->getContentTemplate($contentEntity);
    }

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
        return $this->sm()->getAdminCurrentSite();
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return AdminService
     */
    public function admin()
    {
        return $this->container->get('BWCMS.Admin')->getManager();
    }

    /**
     * @return CacheService
     */
    public function cache()
    {
        return $this->container->get('BWCMS.Cache')->getManager();
    }

    /**
     * @return ACLService
     */
    public function acl()
    {
        return $this->container->get('BWCMS.ACL')->getManager();
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
     * @return ContentQueryService
     */
    public function cq()
    {
        return $this->container->get('BWCMS.ContentQuery')->getManager();
    }

    /**
     * @return SearchService
     */
    public function search()
    {
        return $this->container->get('BWCMS.Search')->getManager();
    }

    /**
     * @return LocaleService
     */
    public function locale()
    {
        return $this->container->get('BWCMS.Locale')->getManager();
    }

    /**
     * @return S3Service
     */
    public function s3Service()
    {
        return $this->container->get('BWCMS.S3')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }

    /**
     * @return ThumbService
     */
    public function getThumbService()
    {
        return $this->container->get('BWCMS.Thumb');
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
     * @return \Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->container->get('event_dispatcher');
    }

    /**
     * @return \JMS\Serializer\Serializer
     */
    public function getSerializer()
    {
        return $this->container->get('serializer');
    }

    /**
     * @param string $message
     */
    public function addSuccessFlash($message)
    {
        $this->addFlash(self::NotifySuccess, $message);
    }

    /**
     * @param string $message
     */
    public function addInfoFlash($message)
    {
        $this->addFlash(self::NotifyInfo, $message);
    }

    /**
     * @param string $message
     */
    public function addWarningFlash($message)
    {
        $this->addFlash(self::NotifyWarning, $message);
    }

    /**
     * @param string $message
     */
    public function addDangerFlash($message)
    {
        $this->addFlash(self::NotifyDanger, $message);
    }

    /**
     * @param string $string
     * @return string
     */
    public function loc($string)
    {
        return call_user_func_array(array($this->locale(), "get"), func_get_args());
    }

    /**
     * @param Request $request
     * @param Array $jsonArray
     * @return Response
     */
    public function returnJsonReponse(Request $request, $jsonArray)
    {
        $serializer = $this->container->get('serializer');
        $serializedReturn = $serializer->serialize($jsonArray, 'json');
        if ($request->query->has('callback')) {
            $callback = $request->query->get('callback');
            $serializedReturn = $callback . '(' . $serializedReturn . ')';
        }
        return new Response($serializedReturn, 200, array('Content-Type' => 'application/json'));
    }

    public function returnErrorResponse($message = 'Unknown error occurred.')
    {
        $response = new Response();
        $response->setStatusCode(500);
        $response->setContent($message);
        return $response;
    }

    public function dump($var, $maxDepth = 2, $stripTags = true)
    {
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }


}
