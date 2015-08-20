<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\FrontEndControllerInterface;
use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;

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

    public function mediaViewAction($siteSlug, $contentId)
    {

        $contentRepository = $this->cm()->getContentRepository();
        /**
         * @var ContentEntity $contentEntity
         */
        $contentEntity = $contentRepository->find($contentId);
        if ($contentEntity == null) {
            throw new NotFoundHttpException('File does not exist');
        }

        if (!$this->mm()->isImage($contentEntity)) {
            throw new NotFoundHttpException('File is not an image');
        }
        /**
         * @var ContentMediaEntity $media
         */
        $media = $contentEntity->getMedia()->first();
        $filename = $this->mm()->checkAndCreateMediaCacheFile($media);

        $response = new BinaryFileResponse($filename);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $media->getFile(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $media->getFile())
        );

        return $response;
    }

}
