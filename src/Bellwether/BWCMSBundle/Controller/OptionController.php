<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Page controller.
 *
 * @Route("/admin/option")
 */
class OptionController extends Controller
{
    /**
     * @Route("/",name="option_home")
     * @Template()
     */
    public function indexAction()
    {
        return array(
                // ...
            );    }

}
