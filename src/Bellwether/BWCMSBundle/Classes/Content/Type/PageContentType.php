<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\BaseContentType;
use Bellwether\BWCMSBundle\Classes\Content\ContentFieldType;
use Symfony\Component\Form\FormEvent;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Classes\Content\ContentTypeInterface;


class PageContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);

        $this->setIsSummaryEnabled(true);
        $this->setIsContentEnabled(true);
        $this->setIsSlugEnabled(true);
        $this->setIsUploadEnabled(false);
    }

    public function buildFields()
    {
        $this->addField('field1', ContentFieldType::String);
        $this->addField('field2', ContentFieldType::String);
        $this->addField('field3', ContentFieldType::String);
        $this->addField('field4', ContentFieldType::String);
        $this->addField('field5', ContentFieldType::DateTime);
        $this->addField('field6', ContentFieldType::Date);
        $this->addField('field7', ContentFieldType::Time);
        $this->addField('heading', ContentFieldType::Serialized);
    }

    public function buildForm()
    {
        $this->fb()->add('field1', 'text',
            array(
                'max_length' => 100,
                'label' => 'field1'
            )
        );

        $this->fb()->add('field2', 'text',
            array(
                'max_length' => 100,
                'label' => 'field2'
            )
        );

        $this->fb()->add('field3', 'text',
            array(
                'max_length' => 100,
                'label' => 'field3'
            )
        );

        $this->fb()->add('field4', 'text',
            array(
                'max_length' => 100,
                'label' => 'field4'
            )
        );

        $this->fb()->add('field5', 'datetime',
            array(
                'label' => 'field5'
            )
        );

        $this->fb()->add('field6', 'date',
            array(
                'label' => 'field6'
            )
        );

        $this->fb()->add('field7', 'time',
            array(
                'label' => 'field7'
            )
        );

        $this->fb()->add('heading', 'bootstrap_collection', array(
            'type' => 'bootstrap_collection',
            'allow_add' => true,
            'allow_delete' => true,
            'add_button_text' => 'Add Level 1',
            'delete_button_text' => 'Delete Level 1',
            'sub_widget_col' => 9,
            'button_col' => 3,
            'options' => array(
                'type' => 'text',
                'allow_add' => true,
                'allow_delete' => true,
                'add_button_text' => 'Add Level 2',
                'delete_button_text' => 'Delete Level 2',
                'prototype_name' => '__subname__',
                'sub_widget_col' => 8,
                'button_col' => 4
            )
        ));
    }

    public function validateForm(FormEvent $event)
    {

    }

    public function loadFormData(ContentEntity $content = null, Form $form = null)
    {
        return $form;
    }

    public function prepareEntity(ContentEntity $content = null, $data = array())
    {
        return $content;
    }

    public function getType()
    {
        return "Page";
    }

    public function getSchema()
    {
        return "Default";
    }

    public function getName()
    {
        return "Page";
    }

}
