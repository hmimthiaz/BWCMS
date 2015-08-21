<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('thumbstyle', new Route('/', array(
    '_controller' => 'BWCMSBundle:ThumbStyleEntity:index',
)));

$collection->add('thumbstyle_show', new Route('/{id}/show', array(
    '_controller' => 'BWCMSBundle:ThumbStyleEntity:show',
)));

$collection->add('thumbstyle_new', new Route('/new', array(
    '_controller' => 'BWCMSBundle:ThumbStyleEntity:new',
)));

$collection->add('thumbstyle_create', new Route(
    '/create',
    array('_controller' => 'BWCMSBundle:ThumbStyleEntity:create'),
    array(),
    array(),
    '',
    array(),
    'POST'
));

$collection->add('thumbstyle_edit', new Route('/{id}/edit', array(
    '_controller' => 'BWCMSBundle:ThumbStyleEntity:edit',
)));

$collection->add('thumbstyle_update', new Route(
    '/{id}/update',
    array('_controller' => 'BWCMSBundle:ThumbStyleEntity:update'),
    array(),
    array(),
    '',
    array(),
    array('POST', 'PUT')
));

$collection->add('thumbstyle_delete', new Route(
    '/{id}/delete',
    array('_controller' => 'BWCMSBundle:ThumbStyleEntity:delete'),
    array(),
    array(),
    '',
    array(),
    array('POST', 'DELETE')
));

return $collection;
