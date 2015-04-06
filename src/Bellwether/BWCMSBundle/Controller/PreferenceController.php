<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * Page controller.
 *
 * @Route("/admin/preference")
 */
class PreferenceController extends BaseController
{
    /**
     * @Route("/{type}.php",name="preference_page")
     * @Template()
     */
    public function indexAction()
    {
        return array(// ...
        );
    }

}
