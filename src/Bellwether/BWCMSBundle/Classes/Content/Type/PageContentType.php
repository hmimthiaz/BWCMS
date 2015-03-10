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

    }

    public function buildForm()
    {

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
