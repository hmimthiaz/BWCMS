<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Symfony\Component\HttpFoundation\Request;


class ACLService extends BaseService
{

    private $roles = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
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

    public function getRoles()
    {
        if (!is_null($this->roles)) {
            return $this->roles;
        }

        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $roles = array_keys($roleHierarchy);

        $this->roles = array();
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleName = str_replace('_', ' ', $role);
                $roleName = ucwords(strtolower($roleName));
                $this->roles[$role] = $roleName;
            }
        }
        return $this->roles;
    }

}