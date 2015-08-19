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
            $this->admin()->init();
            $this->cm()->init();
            $this->mm()->init();
            $this->pref()->init();
            $this->sm()->init();
            $this->tp()->init();
            $this->locale()->init();
        }
        $this->loaded = true;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {

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

        if ($controller[0] instanceof FrontEndControllerInterface) {
            $request = $event->getRequest();
            $params = $request->attributes->get('_route_params');
            if (!isset($params['siteSlug']) || empty($params['siteSlug'])) {
                throw new NotFoundHttpException("Unable to detect language");
            }
            $siteEntity = $this->sm()->getSiteBySlug($params['siteSlug']);
            if ($siteEntity == null) {
                throw new NotFoundHttpException("Language does not exists");
            }
            $this->sm()->setCurrentSite($siteEntity);
            $this->tp()->setSkin($siteEntity->getSkinFolderName());
            return;
        }

        if ($controller[0] instanceof SecurityController) {
            $currentSite = $this->sm()->getAdminCurrentSite();
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
            $this->tp()->setSkin($currentSite->getSkinFolderName());
            return;
        }
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

        if(!empty($displayTemplate)){
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
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
    }

}