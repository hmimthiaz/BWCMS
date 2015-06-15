<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Symfony\Component\Form\FormEvent;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\UriVoter;
use Knp\Menu\Renderer\TwigRenderer;


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
    }

    public function buildFields()
    {
        $this->addField('linkType', ContentFieldType::String);
        $this->addField('linkContent', ContentFieldType::Content);
        $this->addField('linkExternal', ContentFieldType::String);

        $this->addField('linkTarget', ContentFieldType::String);
        $this->addField('linkClass', ContentFieldType::String);
    }

    public function buildForm($isEditMode = false)
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

    public function addTemplates()
    {
        $this->addTemplate('Default','Default.html.twig','Default.png');
    }

    /**
     * @param ContentEntity $contentEntity
     * @param array $options
     * @return string
     */
    public function render($contentEntity, $options = array())
    {
        $contentMenuItems = $this->cm()->getContentMenuItems($contentEntity);
        /**
         * @var \Knp\Menu\MenuItem $rootMenu
         * @var \Knp\Menu\MenuItem $menu
         *
         */
        $menu = array();
        $rootMenu = $options['factory']->createItem($contentEntity->getSlug());
        $rootMenu->setChildrenAttribute('id', $options['id']);
        $rootMenu->setChildrenAttribute('class', $options['class']);
        $menu[$contentEntity->getId()] = $rootMenu;
        /**
         * @var ContentEntity $content
         */
        foreach ($contentMenuItems as $content) {
            $menu[$content->getId()] = $menu[$content->getTreeParent()->getId()]->addChild($content->getTitle(), array('uri' => '#'));
            $contentMeta = $this->cm()->getContentAllMeta($content);
            if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'link') {
                if (isset($contentMeta['linkExternal']) && !empty($contentMeta['linkExternal'])) {
                    $menu[$content->getId()]->setUri($contentMeta['linkExternal']);
                }
            }
            if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'content') {
                if (isset($contentMeta['linkContent']) && ($contentMeta['linkContent'] instanceof ContentEntity)) {
                    $contentLinkURL = $this->cm()->getPublicURL($contentMeta['linkContent']);
                    $menu[$content->getId()]->setUri($contentLinkURL);
                }
            }
            if (isset($contentMeta['linkTarget']) && !empty($contentMeta['linkTarget'])) {
                $menu[$content->getId()]->setLinkAttribute('target', $contentMeta['linkTarget']);
            }
            if (isset($contentMeta['linkClass']) && !empty($contentMeta['linkClass'])) {
                $menu[$content->getId()]->setLinkAttribute('class', $contentMeta['linkClass']);
            }
        }

        $requestURL = $this->getRequest()->getRequestUri();
        $menuTemplate = $this->getContentTemplate($contentEntity);
        $matcher = new Matcher();
        $voter = new UriVoter($requestURL);
        $matcher->addVoter($voter);

        $renderer = new TwigRenderer($options['environment'], $menuTemplate, $matcher);
        return $renderer->render($menu[$contentEntity->getId()]);
    }

    /**
     * @return Request|null
     */
    public function getRequest(){
        return $this->requestStack->getCurrentRequest();
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
        return "Navigation";
    }

    public function getSchema()
    {
        return "Folder";
    }

    public function getName()
    {
        return "Folder";
    }

}
