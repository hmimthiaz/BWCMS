<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\BaseContentType;
use Bellwether\BWCMSBundle\Classes\Content\ContentFieldType;


class MediaContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function buildFields()
    {
        $this->addField('title', ContentFieldType::String);
        $this->addField('summary', ContentFieldType::String);
        $this->addField('content', ContentFieldType::String);
    }

    public function buildForm()
    {
        $this->fb()->add('title');
        $this->fb()->add('summary');
        $this->fb()->add('content');
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
