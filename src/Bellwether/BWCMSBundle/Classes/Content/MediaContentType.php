<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class MediaContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function getType()
    {
        return "Media";
    }

    public function getSchema()
    {
        return "Default";
    }

}
