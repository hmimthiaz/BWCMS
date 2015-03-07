<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class PageContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function getType()
    {
        return "Page";
    }

    public function getSchema()
    {
        return "Default";
    }

}
