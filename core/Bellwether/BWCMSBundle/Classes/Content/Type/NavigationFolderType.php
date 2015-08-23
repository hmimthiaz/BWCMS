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
        $this->addField('linkCaption', ContentFieldType::String);
        $this->addField('linkType', ContentFieldType::String);
        $this->addField('linkContent', ContentFieldType::Content);
        $this->addField('linkExternal', ContentFieldType::String);
        $this->addField('linkRoute', ContentFieldType::String);

        $this->addField('linkTarget', ContentFieldType::String);
        $this->addField('linkClass', ContentFieldType::String);
        $this->addField('liClass', ContentFieldType::String);
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
    }

    public function addTemplates()
    {
        $this->addTemplate('Default', 'Default.html.twig', 'Default.png');
    }

    /**
     * @param ContentEntity $contentEntity
     * @param array $options
     * @return string
     */
    public function render($contentEntity, $options = array())
    {
        $contentMenuItems = $this->cq()->getContentMenuItems($contentEntity);
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
            $menu[$content->getId()] = $menu[$content->getTreeParent()->getId()]->addChild($content->getSlug(), array('uri' => '#'));
            if (isset($options['emptyTitle']) && $options['emptyTitle'] === true) {
                $menu[$content->getId()]->setLabel('');
            } else {
                $menu[$content->getId()]->setLabel($content->getTitle());
            }
            $contentMeta = $this->cm()->getContentAllMeta($content);
            if (isset($contentMeta['linkCaption']) && !empty($contentMeta['linkCaption'])) {
                $menu[$content->getId()]->setLabel($contentMeta['linkCaption']);
                $menu[$content->getId()]->setExtra('safe_label', true);
            }
            if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'route') {
                if (isset($contentMeta['linkRoute']) && !empty($contentMeta['linkRoute'])) {
                    $linkURL = $this->tp()->getCurrentSkin()->getNavigationRoute($contentMeta['linkRoute']);
                    $menu[$content->getId()]->setUri($linkURL);
                }
            }
            if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'link') {
                if (isset($contentMeta['linkExternal']) && !empty($contentMeta['linkExternal'])) {
                    $menu[$content->getId()]->setUri($contentMeta['linkExternal']);
                }
            }
            if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'content') {
                if (isset($contentMeta['linkContent']) && ($contentMeta['linkContent'] instanceof ContentEntity)) {
                    $contentLinkURL = $this->cq()->getPublicURL($contentMeta['linkContent']);
                    $menu[$content->getId()]->setUri($contentLinkURL);
                }
            }
            if (isset($contentMeta['linkTarget']) && !empty($contentMeta['linkTarget'])) {
                $menu[$content->getId()]->setLinkAttribute('target', $contentMeta['linkTarget']);
            }
            if (isset($contentMeta['linkClass']) && !empty($contentMeta['linkClass'])) {
                $menu[$content->getId()]->setLinkAttribute('class', $contentMeta['linkClass']);

            }
            if (isset($contentMeta['liClass']) && !empty($contentMeta['liClass'])) {
                $menu[$content->getId()]->setAttribute('class', $contentMeta['liClass']);
            }
            if (isset($contentMeta['linkImage']) && !empty($contentMeta['linkImage'])) {
                $menu[$content->getId()]->setExtra('image', $contentMeta['linkImage']);
            }
            if (isset($contentMeta['linkDescription']) && !empty($contentMeta['linkDescription'])) {
                $menu[$content->getId()]->setExtra('summary', $contentMeta['linkDescription']);
            }
        }

        if (isset($options['template']) && !empty($options['template'])) {
            $menuTemplate = $this->tp()->getCurrentSkin()->getTemplateName($options['template']);
        } else {
            $menuTemplate = $this->getContentTemplate($contentEntity);
        }

        $requestURL = $this->getRequest()->getRequestUri();
        $matcher = new Matcher();
        $voter = new UriVoter($requestURL);
        $matcher->addVoter($voter);

        $rendererOptions = array();
        if (isset($options['currentClass']) && !empty($options['currentClass'])) {
            $rendererOptions['currentClass'] = $options['currentClass'];
        }
        if (isset($options['firstClass']) && !empty($options['firstClass'])) {
            $rendererOptions['firstClass'] = $options['firstClass'];
        }
        if (isset($options['lastClass']) && !empty($options['lastClass'])) {
            $rendererOptions['lastClass'] = $options['lastClass'];
        }
        if (isset($options['allow_safe_labels']) && !empty($options['allow_safe_labels'])) {
            $rendererOptions['allow_safe_labels'] = $options['allow_safe_labels'];
        }
        $renderer = new TwigRenderer($options['environment'], $menuTemplate, $matcher, $rendererOptions);
        return $renderer->render($menu[$contentEntity->getId()]);
    }

    /**
     * @return Request|null
     */
    public function getRequest()
    {
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
        return '@BWCMSBundle/Resources/icons/content/Folder.png';
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
