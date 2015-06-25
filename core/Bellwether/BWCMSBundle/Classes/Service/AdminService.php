<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;


class AdminService extends BaseService
{
    private $isAdmin;

    private $factory;

    function __construct(FactoryInterface $factory = null, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setFactory($factory);
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return AdminService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * Service Init.
     */
    public function init()
    {
        $this->setIsAdmin(false);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function buildRightMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        $currentSite = $this->sm()->getAdminCurrentSite();
        $allSites = $this->sm()->getAllSites();
        $menu->addChild('Site', array('uri' => '#', 'label' => 'Site: ' . $currentSite->getName()))->setAttribute('dropdown', true);
        $menu['Site']->addChild('View Site', array(
            'route' => 'home',
            'routeParameters' => array('siteSlug' => $currentSite->getSlug())
        ))->setLinkAttributes(array('target' => '_blank'));
        if (count($allSites) > 1) {
            $menu['Site']->addChild('-', array('uri' => '#'))->setAttribute('divider', true);
            foreach ($allSites as $siteInfo) {
                /**
                 * @var \Knp\Menu\MenuItem $siteMenu
                 */
                $siteMenu = $menu['Site']->addChild($siteInfo->getName(), array(
                    'route' => 'site_change_current',
                    'routeParameters' => array('siteId' => $siteInfo->getId())
                ));
                if ($currentSite->getId() == $siteInfo->getId()) {
                    $siteMenu->setCurrent(true);
                }
            }
        }

        $displayName = $this->getUser()->getFirstName();
        if (empty($displayName)) {
            $displayName = $this->getUser()->getEmail();
        }
        $menu->addChild('Profile', array('uri' => '#', 'label' => $displayName))->setAttribute('dropdown', true);
        $menu['Profile']->addChild('Profile', array('route' => 'user_profile'));
        $menu['Profile']->addChild('Change Password', array('route' => 'user_change_password'));
        if ($this->getSecurityContext()->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $menu['Profile']->addChild('Exit User', array(
                'route' => 'user_home',
                'routeParameters' => array('_switch_user' => '_exit')
            ));
        }
        $menu['Profile']->addChild('Logout', array('route' => 'user_logout'));

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
                'type' => 'content'
            )
        ));
        $menu['Manage']->addChild('Media', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'media'
            )
        ));
        $menu['Manage']->addChild('Navigation', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'navigation'
            )
        ));
        $menu['Manage']->addChild('Widget', array(
            'route' => 'content_home',
            'routeParameters' => array(
                'type' => 'widget'
            )
        ));
        $menu['Manage']->addChild('-', array('uri' => '#'))->setAttribute('divider', true);


        $taxonomyContentTypes = $this->cm()->getTaxonomyContentTypes();
        if (!empty($taxonomyContentTypes)) {
            foreach ($taxonomyContentTypes as $cType) {
                $class = $cType['class'];
                $menu['Manage']->addChild($class->getName(), array(
                    'route' => 'taxonomy_home',
                    'routeParameters' => array(
                        'schema' => $class->getSchema()
                    )
                ));
            }
            $menu['Manage']->addChild('--', array('uri' => '#'))->setAttribute('divider', true);
        }

        $menu['Manage']->addChild('Image Thumb Styles', array(
            'route' => 'thumbstyle_home'
        ));


        if ($this->isGranted('ROLE_PREFERENCE')) {
            $menu->addChild('Preference', array('uri' => '#', 'label' => 'Preference'))->setAttribute('dropdown', true);
            $registeredOptionTypes = $this->pref()->getRegisteredOptionTypes();
            foreach ($registeredOptionTypes as $optionType) {
                $menu['Preference']->addChild($optionType['name'], array(
                    'route' => 'preference_page',
                    'routeParameters' => array(
                        'type' => $optionType['type']
                    )
                ));
            }
        }

        $menu->addChild('Admin', array('uri' => '#', 'label' => 'Admin'))->setAttribute('dropdown', true);

        $menu['Admin']->addChild('Site', array(
            'route' => 'site_home'
        ));
        $menu['Admin']->addChild('User', array(
            'route' => 'user_home'
        ));
        $menu['Admin']->addChild('-*-', array('uri' => '#'))->setAttribute('divider', true);
        $menu['Admin']->addChild('About', array(
            'route' => 'about_home'
        ));
        return $menu;
    }

    /**
     * @return bool
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param bool $isAdmin
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }


    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

}
