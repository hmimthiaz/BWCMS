<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\CachedSiteFrontEndControllerInterface;
use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;
use Symfony\Component\HttpFoundation\Request;

class MediaFrontEndController extends BaseController implements CachedSiteFrontEndControllerInterface
{

    public function mediaThumbAction($siteSlug, $contentId, $thumbSlug, $scale)
    {
        $thumbEntity = $this->cache()->fetch('thumbStyle_' . $thumbSlug);
        if (empty($thumbEntity)) {
            $thumbEntity = $this->mm()->getThumbStyle($thumbSlug, $this->sm()->getCurrentSite());
            if (empty($thumbEntity)) {
                throw new NotFoundHttpException('Thumb not found');
            }
            $this->cache()->save('thumbStyle_' . $thumbSlug, $thumbEntity);
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
                throw new NotFoundHttpException('File not found');
            }
            $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
            $this->cache()->save('contentMedia_' . $contentId, $contentMediaEntity);
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

        $thumbCache = $thumb->cacheFile('guess', $thumbEntity->getQuality(), true);
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
                throw new NotFoundHttpException('File not found');
            }
            if (!$this->mm()->isImage($contentEntity)) {
                throw new NotFoundHttpException('File is not an image');
            }

            $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
            $this->cache()->save('contentMedia_' . $contentId, $contentMediaEntity);
        }
        $filename = $this->mm()->getMediaCachePath($contentMediaEntity);
        $response = new BinaryFileResponse($filename);
        $response->trustXSendfileTypeHeader();
        $lastModified = $contentMediaEntity->getContent()->getModifiedDate();
        if (is_null($lastModified)) {
            $lastModified = $contentMediaEntity->getContent()->getCreatedDate();
        }
        if (!is_null($lastModified)) {
            $response->setLastModified($lastModified);
        }
        $expiresDate = new \DateTime();
        $expiresDate->add(new \DateInterval('P30D'));
        $response->setExpires($expiresDate);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $contentMediaEntity->getFile(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $contentMediaEntity->getFile())
        );

        return $response;
    }

    public function downloadMediaAction(Request $request, $siteSlug, $contentId)
    {
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
                throw new NotFoundHttpException('File not found');
            }
            $this->mm()->checkAndCreateMediaCacheFile($contentMediaEntity);
            $this->cache()->save('contentMedia_' . $contentId, $contentMediaEntity);
        }
        $filename = $this->mm()->getMediaCachePath($contentMediaEntity);
        $response = new BinaryFileResponse($filename);
        $response->trustXSendfileTypeHeader();
        $lastModified = $contentMediaEntity->getContent()->getModifiedDate();
        if (is_null($lastModified)) {
            $lastModified = $contentMediaEntity->getContent()->getCreatedDate();
        }
        if (!is_null($lastModified)) {
            $response->setLastModified($lastModified);
        }
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        if ('image/svg+xml' == $contentMediaEntity->getMime()) {
            $disposition = ResponseHeaderBag::DISPOSITION_INLINE;
        }
        $expiresDate = new \DateTime();
        $expiresDate->add(new \DateInterval('P30D'));
        $response->setExpires($expiresDate);
        $response->setContentDisposition(
            $disposition,
            $contentMediaEntity->getFile() . '.' . $contentMediaEntity->getExtension(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $contentMediaEntity->getFile() . '.' . $contentMediaEntity->getExtension())
        );

        return $response;
    }

}
