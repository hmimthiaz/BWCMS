<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('bwcms_homepage', new Route('/hello/{name}', array(
    '_controller' => 'BWCMSBundle:Default:index',
)));

return $collection;
