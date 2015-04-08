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
    function __construct($kernel, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
//        $this->dump($kernel);
//        exit;

        $this->setContainer($container);

//        $container->loadFromExtension('swiftmailer', array(
//            'transport'  => "smtp",
//            'encryption' => "ssl",
//            'auth_mode'  => "login",
//            'host'       => "smtp.gmail.com",
//            'username'   => "your_username",
//            'password'   => "your_password",
//        ));

//        $container->setParameter('swiftmailer.mailer.default.transport.smtp.port',25);

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