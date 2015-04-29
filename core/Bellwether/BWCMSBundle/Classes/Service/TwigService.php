<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManager;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\UriVoter;
use Knp\Menu\Renderer\ListRenderer;
use Knp\Menu\Renderer\TwigRenderer;
use Gregwar\Image\Image;


class TwigService extends BaseService implements \Twig_ExtensionInterface
{

    private $factory;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    function __construct(FactoryInterface $factory = null, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setFactory($factory);
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     *
     * @param \Twig_Environment $environment The current Twig_Environment instance
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->setEnvironment($environment);
    }

    /**
     * @return \Twig_Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param \Twig_Environment $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array();
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return \Twig_NodeVisitorInterface[] An array of Twig_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return array();
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return array An array of tests
     */
    public function getTests()
    {
        return array();
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'link' => new \Twig_Function_Method($this, 'getContentLink'),
            'menu' => new \Twig_Function_Method($this, 'getContentMenuBySlug'),
            'meta' => new \Twig_Function_Method($this, 'getContentMeta'),
            'thumb' => new \Twig_Function_Method($this, 'getThumbImage')
        );
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators()
    {
        return array();
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return array();
    }

    /**
     * @param ContentEntity $contentEntity
     * @return null|string
     */
    public function getContentLink($contentEntity)
    {
        return $this->cm()->getPublicURL($contentEntity);
    }

    /**
     * @param string $slug
     * @return string
     */
    public function getContentMenuBySlug($slug)
    {
        $contentEntity = $this->cm()->getContentBySlugPath($slug);
        if (is_null($contentEntity)) {
            return '';
        }
        $contentMenuItems = $this->cm()->getContentMenuItemsBySlug($contentEntity);
        /**
         * @var \Knp\Menu\MenuItem $rootMenu
         * @var \Knp\Menu\MenuItem $menu
         *
         */
        $menu = array();
        $rootMenu = $this->factory->createItem($contentEntity->getSlug());
        $rootMenu->setChildrenAttribute('class', 'menu');
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
        $menuTemplate = $this->tp()->getCurrentSkin()->getTemplateName($this->cm()->getContentTemplate($contentEntity));
        $matcher = new Matcher();
        $voter = new UriVoter($requestURL);
        $matcher->addVoter($voter);

        $renderer = new TwigRenderer($this->getEnvironment(), $menuTemplate, $matcher);
        return $renderer->render($menu[$contentEntity->getId()]);
    }

    /**
     * @param $contentEntity
     * @param $metaKey
     * @param bool $default
     * @return bool
     */
    public function getContentMeta($contentEntity, $metaKey, $default = false)
    {
        return $this->cm()->getContentMeta($contentEntity, $metaKey, $default);
    }


    public function getThumbImage($contentEntity, $thumbSlug)
    {
        $thumbInfo = $this->mm()->getThumbStyle($thumbSlug, $this->sm()->getCurrentSite());
        return $this->mm()->getContentThumbURLWithStyle($contentEntity, $thumbInfo);
    }

    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
    }


    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'BWCMS_Twig_Extras';
    }
}