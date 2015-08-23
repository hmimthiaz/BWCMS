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

class NavigationLinkType Extends ContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);

        $this->setIsHierarchy(false);
        $this->setIsRootItem(false);

        $this->setIsSummaryEnabled(false);
        $this->setIsContentEnabled(false);
        $this->setIsUploadEnabled(false);
    }

    public function buildFields()
    {
        $this->addField('linkCaption', ContentFieldType::String);
        $this->addField('linkType', ContentFieldType::String);
        $this->addField('linkContent', ContentFieldType::Content);
        $this->addField('linkExternal', ContentFieldType::String);
        $this->addField('linkRoute', ContentFieldType::String);

        $this->addField('linkTarget', ContentFieldType::String);
        $this->addField('linkClass', ContentFieldType::String);
        $this->addField('liClass', ContentFieldType::String);

        $this->addField('linkImage', ContentFieldType::Content);
        $this->addField('linkDescription', ContentFieldType::String);
    }

    public function buildForm($isEditMode = false)
    {
        $routes = $this->tp()->getCurrentSkin()->getNavigationRoutes();
        $routes = array_merge(array('' => ''), $routes);

        $this->fb()->add('linkCaption', 'text',
            array(
                'required' => false,
                'label' => 'Caption',
            )
        );

        $this->fb()->add('linkType', 'choice',
            array(
                'label' => 'Type',
                'choices' => array('content' => 'Content', 'link' => 'Link', 'route' => 'Route Rule'),
            )
        );

        $this->fb()->add('linkContent', 'bwcms_content',
            array(
                'required' => false,
                'label' => 'Content'
            )
        );

        $this->fb()->add('linkExternal', 'text',
            array(
                'required' => false,
                'label' => 'Link',
            )
        );

        $this->fb()->add('linkRoute', 'choice',
            array(
                'required' => false,
                'label' => 'Route',
                'choices' => $routes,
            )
        );

        $this->fb()->add('linkTarget', 'choice',
            array(
                'required' => false,
                'label' => 'Target',
                'choices' => array('_self' => 'Same Window', '_blank' => 'New Window'),
            )
        );

        $this->fb()->add('linkClass', 'text',
            array(
                'required' => false,
                'label' => 'Link Class',
            )
        );

        $this->fb()->add('liClass', 'text',
            array(
                'required' => false,
                'label' => 'Li Class',
            )
        );

        $this->fb()->add('linkImage', 'bwcms_content',
            array(
                'required' => false,
                'label' => 'Image',
                'contentType' => 'Media',
                'schema' => 'File',
                'onlyImage' => true
            )
        );

        $this->fb()->add('linkDescription', 'text',
            array(
                'required' => false,
                'label' => 'Summary',
            )
        );
    }

    public function addTemplates()
    {
        $this->addTemplate('Default', 'Default.html.twig', 'Default.png');
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
     * @return string
     */
    public function getImage()
    {
        return '@BWCMSBundle/Resources/icons/content/Page.png';
    }

    /**
     * @param ContentEntity $contentEntity
     * @return string|null
     */
    public function getPublicURL($contentEntity, $full = false)
    {
        return null;
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
        return "Navigation";
    }

    public function getSchema()
    {
        return "Link";
    }

    public function getName()
    {
        return "Link";
    }

}
