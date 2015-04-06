<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Page controller.
 *
 * @Route("/admin/preference")
 */
class PreferenceController extends Controller
{
    /**
     * @Route("/",name="preference_home")
     * @Template()
     */
    public function indexAction()
    {
        return array(
                // ...
            );    }

}
