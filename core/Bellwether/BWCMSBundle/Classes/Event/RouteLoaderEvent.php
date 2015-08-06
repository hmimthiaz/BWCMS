<?php

namespace Bellwether\BWCMSBundle\Classes\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


class RouteLoaderEvent extends Event
{
    /**
     * @var RouteCollection
     */
    private $routes;

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param RouteCollection $routes
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }

}