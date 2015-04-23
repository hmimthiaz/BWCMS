<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FrontEndController extends BaseController implements FrontEndControllerInterface
{
    /**
     * @Template()
     */
    public function homeAction($siteSlug)
    {
        $template = $this->tp()->getCurrentSkin()->getHomePageTemplate();
        $templateVariables = array();

        return $this->render($template, $templateVariables);
    }

    /**
     * @Template()
     */
    public function contentFolderAction($siteSlug, $folderSlug)
    {
        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug);

        $template = $this->tp()->getCurrentSkin()->getContentTemplate($contentEntity);

        $contentItems = $this->cm()->getFolderItems($contentEntity);

        $templateVariables = array(
            'content' => $contentEntity,
            'items' => $contentItems['items']
        );

        return $this->render($template, $templateVariables);
    }

    /**
     * @Template()
     */
    public function contentPageAction($siteSlug, $folderSlug, $pageSlug)
    {

        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug.'/'.$pageSlug);

        $template = $this->tp()->getCurrentSkin()->getContentTemplate($contentEntity);
        $templateVariables = array(
            'content' => $contentEntity
        );

        return $this->render($template, $templateVariables);
    }

}
