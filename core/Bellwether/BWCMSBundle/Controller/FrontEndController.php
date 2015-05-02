<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\FrontEndControllerInterface;
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
        $templateVariables = array();
        $template = $this->tp()->getCurrentSkin()->getHomePageTemplate();
        return $this->render($template, $templateVariables);
    }

    /**
     * @Template()
     */
    public function contentFolderAction($siteSlug, $folderSlug)
    {
        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug);
        if (empty($contentEntity)) {
            throw new NotFoundHttpException('Folder does not exist');
        }

        $contentItems = $this->cm()->getFolderItems($contentEntity);

        $templateVariables = array(
            'content' => $contentEntity,
            'items' => $contentItems['items']
        );
        $template = $this->tp()->getCurrentSkin()->getContentTemplate($contentEntity);
        return $this->render($template, $templateVariables);
    }

    /**
     * @Template()
     */
    public function contentPageAction($siteSlug, $folderSlug, $pageSlug)
    {
        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug.'/'.$pageSlug);
        if (empty($contentEntity)) {
            throw new NotFoundHttpException('Page does not exist');
        }

        $templateVariables = array(
            'content' => $contentEntity
        );
        $template = $this->tp()->getCurrentSkin()->getContentTemplate($contentEntity);
        return $this->render($template, $templateVariables);
    }

}
