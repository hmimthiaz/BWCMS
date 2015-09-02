<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\FrontEndControllerInterface;
use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\Common\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;
use Symfony\Component\HttpFoundation\Request;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;

use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\ContentQueryService;

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

    public function searchAction(Request $request, $siteSlug)
    {
        $searchString = $request->query->get('query');
        if (!empty($searchString)) {
            $searchString = filter_var($searchString, FILTER_SANITIZE_STRING);
        }

        $pager = new Pagination($request, 5);
        $pager = $this->search()->searchIndex($searchString, $pager);
        $returnVar['pager'] = $pager;
        $returnVar['searchString'] = $searchString;

        $template = $this->tp()->getCurrentSkin()->getSearchTemplate();
        return $this->render($template, $returnVar);
    }

    public function mediaThumbAction($siteSlug, $contentId, $thumbSlug, $scale)
    {
        $thumbEntity = $this->cache()->fetch('thumbStyle_' . $thumbSlug);
        if (empty($thumbEntity)) {
            $thumbEntity = $this->mm()->getThumbStyle($thumbSlug, $this->sm()->getCurrentSite());
            if (empty($thumbEntity)) {
                throw new $this->createNotFoundException();
            }
            $this->cache()->save('thumbStyle_' . $thumbSlug, $thumbEntity, 600);
        }

        /**
         * @var ContentMediaEntity $contentMediaEntity
         */
        $contentMediaEntity = $this->cache()->fetch('contentMedia_' . $contentId);
        if (empty($contentMediaEntity)) {
            /**
             * @var ContentEntity $contentEntity
             */
            $contentEntity = $this->cm()->getContentRepository()->find($contentId);
            $contentMediaEntity = $contentEntity->getMedia()->first();
            if (empty($contentMediaEntity)) {
                throw new $this->createNotFoundException();
            }
            $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
            $this->cache()->save('contentMedia_' . $contentId, $contentMediaEntity, 600);
        }

        $filename = $this->mm()->getMediaCachePath($contentMediaEntity);
        $thumb = $this->getThumbService()->open($filename);
        $width = $thumbEntity->getWidth() * $scale;
        $height = $thumbEntity->getHeight() * $scale;
        switch ($thumbEntity->getMode()) {
            case 'resize':
                $thumb = $thumb->resize($width, $height);
                break;
            case 'scaleResize':
                $thumb = $thumb->scaleResize($width, $height);
                break;
            case 'forceResize':
                $thumb = $thumb->forceResize($width, $height);
                break;
            case 'cropResize':
                $thumb = $thumb->cropResize($width, $height);
                break;
            case 'zoomCrop':
                $thumb = $thumb->zoomCrop($width, $height);
                break;
        }

        $thumbCache = $this->mm()->getWebRoot() . $thumb->cacheFile('guess');
        $response = new BinaryFileResponse($thumbCache);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $contentMediaEntity->getFile(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $contentMediaEntity->getFile())
        );

        return $response;
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
