<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BWCMSBundle:Default:index.html.twig', array('name' => $name));
    }
}
