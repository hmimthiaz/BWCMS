<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Bellwether\BWCMSBundle\Classes\Content\ContentFieldType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\BaseContentType;
use Bellwether\BWCMSBundle\Classes\Content\ContentFieldType;


class PageContentType Extends BaseContentType
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
        $this->fb()->add('title', 'text',
            array(
                'max_length' => 100,
                'required' => true,
                'label' => 'Title'
            )
        );

        $this->fb()->add('summary', 'textarea',
            array(
                'max_length' => 100,
                'required' => false,
                'label' => 'Summary'
            )
        );

        $this->fb()->add('content', 'textarea',
            array(
                'max_length' => 100,
                'required' => false,
                'label' => 'Content'
            )
        );
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
