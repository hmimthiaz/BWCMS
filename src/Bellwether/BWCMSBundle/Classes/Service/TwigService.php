<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManager;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Renderer\ListRenderer;


class TwigService extends BaseService implements \Twig_ExtensionInterface
{

    private $factory;

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
            'menu' => new \Twig_Function_Method($this, 'getContentMenuBySlug')
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
         * @var \Knp\Menu\MenuItem $menu
         */
        $menu = array();
        $menu[$contentEntity->getId()] = $this->factory->createItem($contentEntity->getSlug());

        $this->dump($menu[$contentEntity->getId()]);

        /**
         * @var ContentEntity $content
         */
        foreach ($contentMenuItems as $content) {
            $menu[$content->getId()] = $menu[$content->getTreeParent()->getId()]->addChild($content->getTitle(), array());
            $menu[$content->getId()]->setUri(rand(1,10));
            $this->dump($content->getMeta());
        }

        $renderer = new ListRenderer(new \Knp\Menu\Matcher\Matcher());
        return $renderer->render($menu[$contentEntity->getId()]);
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