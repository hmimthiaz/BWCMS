<?php

namespace Bellwether\BWCMSBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

class RoutingLoader extends BaseService implements LoaderInterface
{
    private $loaded = false;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }


    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $defaultSite = $this->sm()->getDefaultSite();

        $routes = new RouteCollection();

        $homeRedirectRoute = new Route('/', array(
            '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
            'path' => '/' . $defaultSite->getSlug() . '/index.php',
            'permanent' => true,
        ));
        $routes->add('homeRedirect', $homeRedirectRoute);

        $homeRedirectRoute = new Route('/{siteSlug}/index.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:home',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+'
        ));
        $routes->add('home', $homeRedirectRoute);


        $contentFolderRoute = new Route('/{siteSlug}/{folderSlug}/index.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:contentFolder',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+',
            'folderSlug' => '[a-zA-Z0-9-_/]+'
        ));
        $routes->add('contentFolder', $contentFolderRoute);

        $contentPageRoute = new Route('/{siteSlug}/{folderSlug}/{pageSlug}.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:contentFolder',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+',
            'folderSlug' => '[a-zA-Z0-9-_/]+',
            'pageSlug' => '[a-zA-Z0-9-_]+'
        ));
        $routes->add('contentPage', $contentPageRoute);

        $this->loaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'dynamic' === $type;
    }

    public function getResolver()
    {
        // needed, but can be blank, unless you want to load other resources
        // and if you do, using the Loader base class is easier (see below)
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}