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


class ContentArticleType Extends ContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);

        $this->setIsHierarchy(false);
        $this->setIsRootItem(false);

        $this->setIsSummaryEnabled(true);
        $this->setIsContentEnabled(true);
        $this->setIsSlugEnabled(true);
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
        return '@BWCMSBundle/Resources/icons/content/Article.png';
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
        return $this->container->get('router')->generate('contentArticle', $parameters,true);
    }

    /**
     * @return null|RouteCollection
     */
    public function getRouteCollection()
    {
        $routes = new RouteCollection();
        $contentArticleRoute = new Route('/{siteSlug}/content/{folderSlug}/{pageSlug}.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:contentPage',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+',
            'folderSlug' => '[a-zA-Z0-9-_/]+',
            'pageSlug' => '[a-zA-Z0-9-_]+'
        ));
        $routes->add('contentArticle', $contentArticleRoute);
        return $routes;
    }


    public function getType()
    {
        return "Content.Page";
    }

    public function getSchema()
    {
        return "Article";
    }

    public function getName()
    {
        return "Article";
    }

}
