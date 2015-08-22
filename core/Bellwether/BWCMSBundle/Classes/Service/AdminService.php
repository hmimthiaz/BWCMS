<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Entity\AuditEntity;
use Bellwether\BWCMSBundle\Classes\Constants\AuditLevelType;
use Bellwether\BWCMSBundle\Classes\Constants\AuditActionType;

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
        if (!$this->loaded) {
            $this->setIsAdmin(false);
        }
        $this->loaded = true;
    }

    public function addAudit($level, $module, $action, $guid, $description)
    {
        $auditLevels = AuditLevelType::getList();
        if (!in_array($level, $auditLevels)) {
            throw new \InvalidArgumentException("Invalid audit level");
        }

        $auditActions = AuditActionType::getList();
        if (!in_array($action, $auditActions)) {
            throw new \InvalidArgumentException("Invalid action level");
        }

        $remoteAddress = $this->container->get('request')->getClientIp();
        $currentUser = $this->getUser();
        if (!($currentUser instanceof UserEntity)) {
            $currentUser = null;
        }
        $audit = new AuditEntity();
        $audit->setLevel($level);
        $audit->setRemoteAddress($remoteAddress);
        $audit->setUser($currentUser);
        $audit->setLogDate(new \DateTime());
        $audit->setModule($module);
        $audit->setAction($action);
        $audit->setGuid($guid);
        $audit->setDescription($description);
        $this->em()->persist($audit);
        $this->em()->flush();
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
            'route' => 'home_page',
            'routeParameters' => array('siteSlug' => $currentSite->getSlug())
        ))->setLinkAttributes(array('target' => '_blank'));
        if (count($allSites) > 1) {
            $menu['Site']->addChild('-', array('uri' => '#'))->setAttribute('divider', true);
            foreach ($allSites as $siteInfo) {
                /**
                 * @var \Knp\Menu\MenuItem $siteMenu
                 */
                $siteMenu = $menu['Site']->addChild($siteInfo->getName(), array(
                    'route' => '_bwcms_admin_site_change_current',
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
        $menu['Profile']->addChild('Profile', array('route' => '_bwcms_admin_user_profile'));
        $menu['Profile']->addChild('Change Password', array('route' => '_bwcms_admin_user_change_password'));
        if ($this->getSecurityContext()->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $menu['Profile']->addChild('Exit User', array(
                'route' => '_bwcms_admin_user_home',
                'routeParameters' => array('_switch_user' => '_exit')
            ));
        }
        $menu['Profile']->addChild('Logout', array('route' => 'user_logout'));

        return $menu;
    }

    public function buildLeftMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild('Dashboard', array('route' => '_bwcms_admin_dashboard_home'));

        $menu->addChild('Manage', array('uri' => '#', 'label' => 'Manage'))->setAttribute('dropdown', true);

        if (count($this->cm()->getRegisteredContentTypes('Content')) > 0) {
            $menu['Manage']->addChild('Content', array(
                'route' => '_bwcms_admin_content_home',
                'routeParameters' => array(
                    'type' => 'content'
                )
            ));
        }

        if (count($this->cm()->getRegisteredContentTypes('Media')) > 0) {
            $menu['Manage']->addChild('Media', array(
                'route' => '_bwcms_admin_content_home',
                'routeParameters' => array(
                    'type' => 'media'
                )
            ));
        }

        if (count($this->cm()->getRegisteredContentTypes('Navigation')) > 0) {
            $menu['Manage']->addChild('Navigation', array(
                'route' => '_bwcms_admin_content_home',
                'routeParameters' => array(
                    'type' => 'navigation'
                )
            ));
        }

        if (count($this->cm()->getRegisteredContentTypes('Widget')) > 0) {
            $menu['Manage']->addChild('Widget', array(
                'route' => '_bwcms_admin_content_home',
                'routeParameters' => array(
                    'type' => 'widget'
                )
            ));
        }

        $menu['Manage']->addChild('-ctc-', array('uri' => '#'))->setAttribute('divider', true);


        $registeredOptionTypes = $this->pref()->getRegisteredOptionTypes();
        $addedPagePreference = false;
        foreach ($registeredOptionTypes as $optionType) {
            $classInstance = $optionType['class'];
            if ($classInstance->isPagePreference()) {
                $menu['Manage']->addChild($optionType['name'], array(
                    'route' => '_bwcms_admin_preference_page',
                    'routeParameters' => array(
                        'type' => $optionType['type']
                    )
                ));
                $addedPagePreference = true;
            }
        }
        if ($addedPagePreference) {
            $menu['Manage']->addChild('-pf-', array('uri' => '#'))->setAttribute('divider', true);
        }


        $taxonomyContentTypes = $this->cm()->getTaxonomyContentTypes();
        if (!empty($taxonomyContentTypes)) {
            foreach ($taxonomyContentTypes as $cType) {
                $class = $cType['class'];
                $menu['Manage']->addChild($class->getName(), array(
                    'route' => '_bwcms_admin_taxonomy_home',
                    'routeParameters' => array(
                        'schema' => $class->getSchema()
                    )
                ));
            }
            $menu['Manage']->addChild('-ct-', array('uri' => '#'))->setAttribute('divider', true);
        }

        $menu['Manage']->addChild('Locale', array(
            'route' => '_bwcms_admin_locale_home'
        ));

        $menu['Manage']->addChild('Thumb Styles', array(
            'route' => '_bwcms_admin_thumbstyle_home'
        ));

        if ($this->isGranted('ROLE_PREFERENCE')) {
            foreach ($registeredOptionTypes as $optionType) {
                $classInstance = $optionType['class'];
                if (!$classInstance->isPagePreference()) {
                    if (!isset($menu['Preference'])) {
                        $menu->addChild('Preference', array('uri' => '#', 'label' => 'Preference'))->setAttribute('dropdown', true);
                    }
                    $menu['Preference']->addChild($optionType['name'], array(
                        'route' => '_bwcms_admin_preference_page',
                        'routeParameters' => array(
                            'type' => $optionType['type']
                        )
                    ));
                }
            }
        }

        $menu->addChild('Admin', array('uri' => '#', 'label' => 'Admin'))->setAttribute('dropdown', true);
        if ($this->isGranted('ROLE_PREFERENCE')) {
            $menu['Admin']->addChild('Site', array(
                'route' => '_bwcms_admin_site_home'
            ));
            $menu['Admin']->addChild('User', array(
                'route' => '_bwcms_admin_user_home'
            ));
            $menu['Admin']->addChild('-*-', array('uri' => '#'))->setAttribute('divider', true);
        }
        $menu['Admin']->addChild('About', array(
            'route' => '_bwcms_admin_dashboard_about'
        ));
        return $menu;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isAdmin;
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
