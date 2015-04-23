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
use Bellwether\BWCMSBundle\Controller\FrontEndControllerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KernelEventListener extends BaseService
{
    function __construct($kernel, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (!is_array($controller)) {
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

        $currentSite = $this->sm()->getAdminCurrentSite();
        $this->tp()->setSkin($currentSite->getSkinFolderName());
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
    }

    public function onKernelFinishRequest(FinishRequestEvent $event)
    {
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
    }
}