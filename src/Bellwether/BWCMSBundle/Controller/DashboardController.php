<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\BWCMSBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Account controller.
 *
 * @Route("/admin/dashboard")
 */
class DashboardController extends BWCMSBaseController
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $x=10;

        //throw new Exception('Division by zero.');

        return array(// ...
        );
    }

}
