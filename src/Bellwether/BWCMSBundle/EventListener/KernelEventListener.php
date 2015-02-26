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

class KernelEventListener extends BaseService
{
    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function onKernelController(FilterControllerEvent $event)
    {
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