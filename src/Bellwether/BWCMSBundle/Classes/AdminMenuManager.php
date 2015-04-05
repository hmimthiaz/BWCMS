<?php

namespace Bellwether\BWCMSBundle\Classes;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;


class AdminMenuManager extends BaseService
{
    private $factory;

    function __construct(FactoryInterface $factory = null, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setFactory($factory);
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return ContentManager
     */
    public function getManager()
    {
        return $this;
    }

    public function buildRightMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Profile', array('uri' => '#', 'label' => $this->getUser()->getEmail()))->setAttribute('dropdown', true);
        $menu['Profile']->addChild('Profile', array('uri' => '#'));
        if ($this->getSecurityContext()->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $menu['Profile']->addChild('Exit User', array(
                'route' => 'Homepage',
                'routeParameters' => array('_switch_user' => '_exit')
            ));
        }
        $menu['Profile']->addChild('Logout', array('route' => 'fos_user_security_logout'));

        return $menu;
    }

    public function buildLeftMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Dashboard', array('route' => 'dashboard_home'));

        $menu->addChild('Manage', array('uri' => '#', 'label' => 'Manage'))->setAttribute('dropdown', true);
        $menu['Manage']->addChild('Content', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'Content'
            )
        ));
        $menu['Manage']->addChild('Media', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'Media'
            )
        ));
        $menu['Manage']->addChild('Navigation', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'Navigation'
            )
        ));
        $menu['Manage']->addChild('Widget', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'Widget'
            )
        ));

        $menu->addChild('Admin', array('uri' => '#', 'label' => 'Admin'))->setAttribute('dropdown', true);
        $menu['Admin']->addChild('User', array(
            'route' => 'user_home'
        ));

        return $menu;
    }


    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

}