<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Account controller.
 *
 * @Route("/admin/media")
 */
class MediaController extends BaseController
{
    /**
     * @Route("/index",name="media_home")
     * @Template()
     */
    public function indexAction()
    {

        $config = $this->container->getParameter('media.path');


        return array(// ...
        );
    }


    /**
     * @Route("/upload.php",name="media_upload")
     * @Method({"POST"})
     * @Template()
     */
    public function uploadAction()
    {

        try {
            $this->mm()->handleUpload();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        } finally{
            return new Response('Ok', 200);
        }

    }


}
