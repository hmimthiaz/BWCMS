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
    public function homeAction($siteSlug)
    {
        $siteEntity = $this->sm()->getSiteBySlug($siteSlug);
        if ($siteEntity == null) {
            throw new NotFoundHttpException("Page not found");
        }

        var_dump($siteEntity);
        exit;
        return array();
    }

    /**
     * @Template()
     */
    public function contentFolderAction($siteSlug, $folderSlug)
    {
        $siteEntity = $this->sm()->getSiteBySlug($siteSlug);
        if ($siteEntity == null) {
            throw new NotFoundHttpException("Page not found");
        }


        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug);

        $this->dump($siteEntity);

        $this->dump($contentEntity);

        exit;
        return array();
    }

    /**
     * @Template()
     */
    public function contentPageAction($siteSlug, $folderSlug, $pageSlug)
    {
        $siteEntity = $this->sm()->getSiteBySlug($siteSlug);
        if ($siteEntity == null) {
            throw new NotFoundHttpException("Page not found");
        }


        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug);

        $this->dump($siteEntity);

        $this->dump($contentEntity);

        exit;
        return array();
    }

}
