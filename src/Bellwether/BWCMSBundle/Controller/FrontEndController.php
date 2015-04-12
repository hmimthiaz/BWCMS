<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontEndController extends BaseController
{
    /**
     * @Template()
     */
    public function homeAction($slug)
    {
        $siteEntity = $this->sm()->getSiteBySlug($slug);
        if ($siteEntity == null) {
            throw new NotFoundHttpException("Page not found");
        }

        var_dump($siteEntity);
        exit;
        return array();
    }

}
