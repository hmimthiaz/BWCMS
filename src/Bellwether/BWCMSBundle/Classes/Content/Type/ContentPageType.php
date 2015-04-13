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
        $this->setIsSlugEnabled(false);
        $this->setIsUploadEnabled(false);
    }

    public function buildFields()
    {
        $this->addField('fieldContent', ContentFieldType::Content);
        $this->addField('gallery', ContentFieldType::Serialized);
    }

    public function buildForm()
    {
        $this->fb()->add('fieldContent', 'bwcms_content',
            array(
                'label' => 'Content'
            )
        );

        $this->fb()->add('gallery', 'bwcms_collection',
            array(
                'type' => new SampleForm(),
                'required' => false,
                'label' => 'Content',
                'allow_add' => true
            )
        );


    }

    public function getTemplates()
    {
        $templates = array();

        $templates['LeftImage.html.twig'] = 'Image Left';
        $templates['RightImage.html.twig'] = 'Image Right';

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
     * @param ContentEntity $contentEntity
     * @return string|null
     */
    public function getPublicURL($contentEntity)
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
        return $this->container->get('router')->generate('contentPage', $parameters);
    }

    /**
     * @return null|RouteCollection
     */
    public function getRouteCollection()
    {
        $routes = new RouteCollection();
        $contentPageRoute = new Route('/{siteSlug}/content/{folderSlug}/{pageSlug}.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:contentFolder',
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
        return "Content.Page";
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
