<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Symfony\Component\Form\FormEvent;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Classes\Content\ContentTypeInterface;
use Bellwether\BWCMSBundle\Classes\Content\Form\SampleForm;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ContentPageType Extends ContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);

        $this->setIsHierarchy(false);
        $this->setIsRootItem(false);

        $this->setIsSummaryEnabled(true);
        $this->setIsContentEnabled(true);
        $this->setIsUploadEnabled(false);

        $this->setIsSlugEnabled(true);
        $this->setIsIndexed(true);
        $this->setIsPageBuilderSupported(true);
    }

    public function buildFields()
    {
        $this->addField('fieldContent', ContentFieldType::Content);
        $this->addField('gallery', ContentFieldType::Serialized);
    }

    public function buildForm($isEditMode = false, ContentEntity $contentEntity = null)
    {
        $this->fb()->add('fieldContent', 'bwcms_content',
            array(
                'label' => 'Content',
                'contentType' => 'Media'
            )
        );

        $this->fb()->add('gallery', 'bwcms_collection',
            array(
                'type' => new SampleForm(),
                'label' => 'Content',
                'allow_add' => true
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
        $contentParents = $this->cm()->getContentRepository()->getPath($contentEntity);
        if (count($contentParents) < 2) {
            return null;
        }
        array_pop($contentParents);
        $folders = array();
        foreach ($contentParents as $parent) {
            $folders[] = $parent->getSlug();
        }
        $parameters = array(
            'folderSlug' => implode('/', $folders),
            'pageSlug' => $contentEntity->getSlug(),
            'siteSlug' => $contentEntity->getSite()->getSlug()
        );
        return $this->container->get('router')->generate('contentPage', $parameters, $full);
    }

    /**
     * @return null|RouteCollection
     */
    public function getRouteCollection()
    {
        $routes = new RouteCollection();
        $contentPageRoute = new Route('/{siteSlug}/content/{folderSlug}/{pageSlug}.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:contentPage',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+',
            'folderSlug' => '[a-zA-Z0-9-_/]+',
            'pageSlug' => '[a-zA-Z0-9-_]+'
        ));
        $routes->add('contentPage', $contentPageRoute);
        return $routes;
    }


    public function getType()
    {
        return "Content";
    }

    public function getSchema()
    {
        return "Page";
    }

    public function getName()
    {
        return "Page";
    }

}
