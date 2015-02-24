<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Account controller.
 *
 * @Route("/admin/media")
 */
class MediaController extends BWCMSBaseController
{
    /**
     * @Route("/index",name="media_home")
     * @Template()
     */
    public function indexAction()
    {

        $config = $this->container->getParameter('media.path');

//        var_dump($config);
        $this->dump($this->media());

        exit();

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

        $this->media()->handleUpload();


        exit();
    }


}
