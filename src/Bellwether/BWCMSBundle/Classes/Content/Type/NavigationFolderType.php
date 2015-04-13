<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Classes\Content\ContentTypeInterface;


class NavigationFolderType Extends ContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);

        $this->setIsHierarchy(true);
        $this->setIsRootItem(true);

        $this->setIsSummaryEnabled(false);
        $this->setIsContentEnabled(false);
        $this->setIsUploadEnabled(false);
        $this->setIsSortEnabled(false);
    }

    public function buildFields()
    {

    }

    public function buildForm()
    {

    }

    public function getTemplates()
    {
        $templates = array();

        $templates['Default.html.twig'] = 'Default';

        return $templates;
    }

    public function validateForm(FormEvent $event)
    {

    }

    public function loadFormData(ContentEntity $content = null, Form $form = null)
    {
        return $form;
    }

    public function prepareEntity(ContentEntity $content = null, Form $form = null)
    {
        return $content;
    }

    /**
     * @return null
     */
    public function getRouteCollection()
    {
        return null;
    }

    public function getType()
    {
        return "Navigation.Folder";
    }

    public function getSchema()
    {
        return "Default";
    }

    public function getName()
    {
        return "Folder";
    }

}
