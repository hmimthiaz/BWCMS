<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('thumbstyle', new Route('/', array(
    '_controller' => 'BWCMSBundle:ThumbStyle:index',
)));

$collection->add('thumbstyle_show', new Route('/{id}/show', array(
    '_controller' => 'BWCMSBundle:ThumbStyle:show',
)));

$collection->add('thumbstyle_new', new Route('/new', array(
    '_controller' => 'BWCMSBundle:ThumbStyle:new',
)));

$collection->add('thumbstyle_create', new Route(
    '/create',
    array('_controller' => 'BWCMSBundle:ThumbStyle:create'),
    array(),
    array(),
    '',
    array(),
    'POST'
));

$collection->add('thumbstyle_edit', new Route('/{id}/edit', array(
    '_controller' => 'BWCMSBundle:ThumbStyle:edit',
)));

$collection->add('thumbstyle_update', new Route(
    '/{id}/update',
    array('_controller' => 'BWCMSBundle:ThumbStyle:update'),
    array(),
    array(),
    '',
    array(),
    array('POST', 'PUT')
));

$collection->add('thumbstyle_delete', new Route(
    '/{id}/delete',
    array('_controller' => 'BWCMSBundle:ThumbStyle:delete'),
    array(),
    array(),
    '',
    array(),
    array('POST', 'DELETE')
));

return $collection;
