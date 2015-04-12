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

    public function getName()
    {
        return 'Generic';
    }


}