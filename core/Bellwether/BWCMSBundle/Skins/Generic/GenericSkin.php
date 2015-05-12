<?php

namespace Bellwether\BWCMSBundle\Skins\Generic;

use Bellwether\BWCMSBundle\Classes\Base\BaseSkin;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GenericSkin extends BaseSkin
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function getLoginTemplate()
    {
        return $this->getTemplateName("Extras/Login.html.twig");
    }

    public function getForgotTemplate()
    {
        return $this->getTemplateName("Extras/Forgot.html.twig");
    }


    public function get404Template()
    {
        return null;
    }

    public function getHomePageTemplate()
    {
        return $this->getTemplateName("Home/Home.html.twig");
    }

    public function getPaginationTemplate()
    {
        return null;
    }

    public function getName()
    {
        return $this->getFolderName();
    }


}