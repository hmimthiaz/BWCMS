<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class PageContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function buildForm()
    {
        $this->fb()->add('title');
        $this->fb()->add('summary');
        $this->fb()->add('content');
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
