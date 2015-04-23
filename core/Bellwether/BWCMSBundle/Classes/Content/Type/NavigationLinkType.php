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
        $this->setIsSortEnabled(false);
    }

    public function buildFields()
    {
        $this->addField('linkType', ContentFieldType::String);
        $this->addField('linkContent', ContentFieldType::Content);
        $this->addField('linkExternal', ContentFieldType::String);

        $this->addField('linkTarget', ContentFieldType::String);
        $this->addField('linkClass', ContentFieldType::String);
    }

    public function buildForm()
    {
        $this->fb()->add('linkType', 'choice',
            array(
                'label' => 'Type',
                'choices' => array('content' => 'Content', 'link' => 'Link'),
            )
        );

        $this->fb()->add('linkContent', 'bwcms_content',
            array(
                'label' => 'Content'
            )
        );

        $this->fb()->add('linkExternal', 'text',
            array(
                'label' => 'Link',
            )
        );
        $this->fb()->add('linkTarget', 'choice',
            array(
                'label' => 'Target',
                'choices' => array('_self' => 'Same Window', '_blank' => 'New Window'),
            )
        );
        $this->fb()->add('linkClass', 'text',
            array(
                'label' => 'Class',
            )
        );

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
     * @return string
     */
    public function getImage()
    {
        return '@BWCMSBundle/Resources/icons/content/Unknown.png';
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
        return "Navigation.Link";
    }

    public function getSchema()
    {
        return "Default";
    }

    public function getName()
    {
        return "Link";
    }

}
