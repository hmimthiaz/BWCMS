<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomeController extends BWCMSBaseController
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array(// ...
        );
    }

}
