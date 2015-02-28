<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\ContentEntity;

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
            $mediaInfo = $this->mm()->handleUpload();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
        if (!empty($mediaInfo)) {
            $content = new ContentEntity();

            $content->setType('Media');
            $content->setSite($this->getSite());

            $content->setTitle($mediaInfo['originalName']);
            $content->setMime($mediaInfo['mimeType']);
            $content->setName($mediaInfo['filename']);
            $content->setSize($mediaInfo['size']);

            $this->cm()->save($content);

        }
        return new Response('Ok', 200);
    }


}
