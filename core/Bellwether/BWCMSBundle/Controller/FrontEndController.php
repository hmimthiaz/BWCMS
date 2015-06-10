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
        $folderTypes = $this->cm()->getRegisteredContentTypes('Content', 'Folder');
        $contentEntity = $this->cm()->getContentBySlugPath($folderSlug, $folderTypes);
        if (empty($contentEntity)) {
            throw new NotFoundHttpException('Folder does not exist');
        }

        $contentItems = $this->cm()->getFolderItems($contentEntity);
        $templateVariables = array(
            'content' => $contentEntity,
            'items' => $contentItems['items']
        );
        $template = $this->getContentTemplate($contentEntity);
        return $this->render($template, $templateVariables);
    }

    /**
     * @Template()
     */
    public function contentPageAction($siteSlug, $folderSlug, $pageSlug)
    {

        $folderTypes = $this->cm()->getRegisteredContentTypes('Content', 'Folder');
        $folderEntity = $this->cm()->getContentBySlugPath($folderSlug, $folderTypes);
        if (empty($folderEntity)) {
            throw new NotFoundHttpException('Page does not exist');
        }

        $pageTypes = $this->cm()->getRegisteredContentTypes('Content', 'Page');
        $pageEntity = $this->cm()->getContentBySlug($pageSlug, $folderEntity, $pageTypes);
        if (empty($pageEntity)) {
            throw new NotFoundHttpException('Page does not exist');
        }
        $templateVariables = array(
            'content' => $pageEntity
        );
        $template = $this->getContentTemplate($pageEntity);
        return $this->render($template, $templateVariables);
    }

}
