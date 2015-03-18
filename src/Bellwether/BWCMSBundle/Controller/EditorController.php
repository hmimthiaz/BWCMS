<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Page controller.
 *
 * @Route("/admin/editor")
 */
class EditorController extends BaseController
{
    /**
     * @Route("/init.js",name="editor_init")
     * @Template()
     */
    public function initAction(Request $request)
    {

        $templateVariables = array();
        $scriptText = $this->renderView('BWCMSBundle:Editor:init.html.twig', $templateVariables);
        $response = new Response($scriptText, 200, array('Content-Type' => 'application/javascript'));
        return $response;
    }

}
