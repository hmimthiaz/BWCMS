<?php

namespace Bellwether\BWCMSBundle\Classes\Base;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Classes\SiteManager;
use Bellwether\BWCMSBundle\Classes\ContentManager;
use Bellwether\BWCMSBundle\Classes\MediaManager;
use Bellwether\BWCMSBundle\Classes\PreferenceManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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

    /**
     * @return PreferenceManager
     */
    public function pref()
    {
        return $this->container->get('BWCMS.Preference')->getManager();
    }

    /**
     * @return Session
     */
    public function session()
    {
        return $this->container->get('session');
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
