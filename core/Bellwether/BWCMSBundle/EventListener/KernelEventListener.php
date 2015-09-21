<?php

namespace Bellwether\BWCMSBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Classes\Base\FrontEndControllerInterface;
use Bellwether\BWCMSBundle\Classes\Base\CachedSiteFrontEndControllerInterface;

use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Bellwether\BWCMSBundle\Controller\SecurityController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Request;


class KernelEventListener extends BaseService implements AccessDeniedHandlerInterface
{

    function __construct($kernel, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
        $this->init();
    }

    public function init()
    {
        if (!$this->loaded) {
            $this->cache()->init();
            $this->admin()->init();
            $this->cm()->init();
            $this->mm()->init();
            $this->pref()->init();
            $this->sm()->init();
            $this->tp()->init();
            $this->locale()->init();
            $this->search()->init();
        }
        $this->loaded = true;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->cache()->checkPageCacheResponse($event);
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof ProfilerController) {
            return;
        }

        if ($controller[0] instanceof FrontEndControllerInterface || $controller[0] instanceof CachedSiteFrontEndControllerInterface) {
            $request = $event->getRequest();
            $params = $request->attributes->get('_route_params');
            if (!isset($params['siteSlug']) || empty($params['siteSlug'])) {
                throw new NotFoundHttpException("Unable to detect language");
            }
            $siteEntity = null;
            if ($controller[0] instanceof CachedSiteFrontEndControllerInterface) {
                $siteEntity = $this->cache()->fetch('Site' . $params['siteSlug']);
                if (empty($siteEntity)) {
                    $siteEntity = $this->sm()->getSiteBySlug($params['siteSlug']);
                    $this->cache()->save('Site' . $params['siteSlug'], $siteEntity);
                }
            } else {
                $siteEntity = $this->sm()->getSiteBySlug($params['siteSlug']);
            }
            if (empty($siteEntity)) {
                throw new NotFoundHttpException("Language does not exists");
            }
            $this->sm()->setCurrentSite($siteEntity);
            $this->cache()->setCurrentSite($siteEntity);
            $this->locale()->setCurrentSite($siteEntity);
            $this->tp()->setSkin($siteEntity->getSkinFolderName());
            return;
        }

        if ($controller[0] instanceof SecurityController) {
            $currentSite = $this->sm()->getAdminCurrentSite();
            $this->sm()->setCurrentSite($currentSite);
            $this->cache()->setCurrentSite($currentSite);
            $this->locale()->setCurrentSite($currentSite);
            $this->tp()->setSkin($currentSite->getSkinFolderName());
            return;
        }

        if ($controller[0] instanceof BackEndControllerInterface) {
            if (!$this->isGranted('ROLE_BACKEND')) {
                $message = 'User: ' . $this->getUser()->getEmail() . ' does not have access to the administration';
                throw new AccessDeniedException($message, null);
            }
            $this->admin()->setIsAdmin(true);
            $currentSite = $this->sm()->getAdminCurrentSite();
            $this->sm()->setCurrentSite($currentSite);
            $this->cache()->setCurrentSite($currentSite);
            $this->locale()->setCurrentSite($currentSite);
            $this->tp()->setSkin($currentSite->getSkinFolderName());

            // Twig global
            $twig = $this->container->get('twig');
            $twig->addGlobal('theme', $currentSite->getAdminColorThemeName());
            return;
        }
    }

    public function isUserLoggedIn()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return true;
        }
        return false;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {

//        $environment = $this->container->get('kernel')->getEnvironment();
//        if ($environment == 'dev') {
//            return;
//        }

        $exception = $event->getException();
        $templateVars = array();
        $templateVars['message'] = $exception->getMessage();

        $displayTemplate = null;
        //SQL Exceptions
        if ($exception instanceof \PDOException) {
            $displayTemplate = 'BWCMSBundle:Common:sql.html.twig';
        }

        //Handle 404
        if ($exception instanceof NotFoundHttpException) {
            //Check Skin
            if (is_null($this->tp()->getCurrentSkin())) {
                $currentSite = $this->sm()->getAdminCurrentSite();
                $this->tp()->setSkin($currentSite->getSkinFolderName());
            }
            $displayTemplate = $this->tp()->getCurrentSkin()->get404Template();
            if (empty($displayTemplate)) {
                $displayTemplate = 'BWCMSBundle:Common:404.html.twig';
            }
        }

        if (!empty($displayTemplate)) {
            $template = $this->container->get('templating');
            $response = new Response($template->render($displayTemplate, $templateVars));
            $event->setResponse($response);
            return;
        }
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $templateVars = array();
        $templateVars['message'] = $accessDeniedException->getMessage();
        if ($this->getSecurityContext()->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $templateVars['securityExitURL'] = $this->generateUrl('user_home', array('_switch_user' => '_exit'));
        }
        $template = $this->container->get('templating');
        $response = new Response($template->render('BWCMSBundle:Common:access-denied.html.twig', $templateVars));
        $response->send();
        exit();
    }


    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $event->getResponse()->headers->set('X-XSS-Protection', '1; mode=block');
        $event->getResponse()->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $event->getResponse()->headers->set('X-Content-Type-Options', 'nosniff');
        $this->cache()->savePageCacheReponse($event);
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
    }

}