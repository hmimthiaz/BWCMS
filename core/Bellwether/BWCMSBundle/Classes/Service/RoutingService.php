<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Event\RouteLoaderEvent;


class RoutingService extends BaseService implements LoaderInterface
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }


    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "dynamic" loader twice');
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
        $routes->add('home_page', $homeRedirectRoute);

        $mediaImageViewRoute = new Route('/{siteSlug}/media/{contentId}/view.php', array(
            '_controller' => 'BWCMSBundle:FrontEnd:mediaView',
        ), array(
            'siteSlug' => '[a-zA-Z0-9-]+',
            'contentId' => '[a-zA-Z0-9-]+'
        ));
        $routes->add('media_image_view', $mediaImageViewRoute);

        //make sure all content types are initialized.
        $this->cm()->init();
        $registerContentTypes = $this->cm()->getAllContentTypes();

        foreach ($registerContentTypes as $contentType) {
            $routeCollection = $contentType->getRouteCollection();
            if (!is_null($routeCollection)) {
                foreach ($routeCollection as $routeName => $routeInfo) {
                    $routes->add($routeName, $routeInfo);
                }
            }
        }
        $routeLoaderEvent = new RouteLoaderEvent();
        $routeLoaderEvent->setRoutes($routes);

        $this->getEventDispatcher()->dispatch('BWCMS.Route.Loader',$routeLoaderEvent);
        $this->loaded = true;
        return $routeLoaderEvent->getRoutes();
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
