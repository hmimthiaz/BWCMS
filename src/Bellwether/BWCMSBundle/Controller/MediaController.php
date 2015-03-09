<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Bellwether\BWCMSBundle\Entity\ContentEntity;

/**
 * Media controller.
 *
 * @Route("/admin/media")
 */
class MediaController extends BaseController
{
    /**
     * @Route("/",name="media_home")
     * @Template()
     */
    public function indexAction()
    {
        /**
         * Get All the root folders
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        $qb = $contentRepository->getChildrenQueryBuilder(null, false);
        $qb->andWhere(" node.type = 'Folder' ");
        $rootFolders = $qb->getQuery()->getResult();

        $jsNodes = array(
            array(
                'id' => 'Root',
                'text' => 'Folders',
                'icon' => 'glyphicon glyphicon-folder-open',
                'parent' => '#',
                'state' => array(
                    'opened' => true,
                    'disabled' => false,
                    'selected' => true
                )
            )
        );
        if (!empty($rootFolders)) {
            /** @var ContentEntity $content */
            foreach ($rootFolders as $content) {
                $jsNode = array();
                $jsNode['id'] = $content->getId();
                $jsNode['text'] = $content->getTitle();
                $jsNode['icon'] = 'glyphicon glyphicon-folder-open';
                if ($content->getTreeParent() != null) {
                    $jsNode['parent'] = $content->getTreeParent()->getId();
                } else {
                    $jsNode['parent'] = 'Root';
                }
                $jsNodes[] = $jsNode;
            }
        }

        return array(
            'jsNodes' => json_encode($jsNodes),
        );
    }


    /**
     * @Route("/data.php",name="media_table_data")
     * @Method({"GET"})
     */
    public function indexDataAction(Request $request)
    {
        $draw = $request->get('draw', 0);
        $start = $request->get('start', 10);
        $length = $request->get('length', 10);
        $parentId = $request->get('parent', 'Root');

        $search = $request->get('search');
        if ($search != null && isset($search['value']) && !empty($search['value'])) {
            $searchString = $search['value'];
        }

        /**
         * Get All the root folders
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        if ($parentId == 'Root') {
            $qb = $contentRepository->getChildrenQueryBuilder(null, false);
        } else {
            $parentFolder = $contentRepository->find($parentId);
            $qb = $contentRepository->getChildrenQueryBuilder($parentFolder, true);
        }
        $qb->andWhere(" node.type = 'Media' ");
        $qb->setFirstResult($start);
        $qb->setMaxResults($length);
        $qb->add('orderBy', 'node.createdDate DESC');

        if (!empty($searchString)) {
            $qb->andWhere(" node.title LIKE :query1 OR node.name LIKE :query2 ");
            $qb->setParameter('query1', '%' . $searchString . '%');
            $qb->setParameter('query2', '%' . $searchString . '%');
        }

        $result = $qb->getQuery()->getResult();
        $totalCount = $qb->select('COUNT(node)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $data = array();
        $data['draw'] = $draw;
        $data['recordsFiltered'] = $totalCount;
        $data['recordsTotal'] = $totalCount;
        $data['data'] = array();

        if (!empty($result)) {
            foreach ($result as $content) {
                $ca = array();
                $ca['DT_RowId'] = $content->getId();
                $ca['title'] = $content->getTitle();
                $ca['name'] = $content->getName();
                $ca['createdDate'] = $content->getCreatedDate()->format('Y-m-d H:i:s');;
                $ca['thumbnail'] = $this->mm()->getSystemThumbURL($content->getName(), $content->getMime(), $content->getExtension(), 64, 64);
                $ca['thumbnail'] = '<img src="' . $ca['thumbnail'] . '"/>';
                if ($this->mm()->isImage($content->getName(), $content->getMime())) {
                    $imageThumb = $this->getImageThumbURL($content->getName(),800,800);
                    $ca['thumbnail'] = '<a href="'.$imageThumb.'" data-title="'.$content->getTitle().'" class="lightBox">' . $ca['thumbnail'] . '</a>';
                }
                $data['data'][] = $ca;
            }
        }
        return $this->returnJsonReponse($request, $data);
    }

    public function getImageThumbURL($filename, $width, $height)
    {
        $publicFilename = $this->mm()->getFilePath($filename);
        $thumbURL = $this->mm()->getThumbService()->open($publicFilename)->cropResize($width, $height)->cacheFile('guess');
        return $thumbURL;
    }

    /**
     * @Route("/folder.php",name="media_folder_save")
     * @Method({"POST"})
     */
    public function saveFolder(Request $request)
    {
        $mode = $request->get('mode');
        $contentId = $request->get('id');
        $parent = $request->get('parent');
        $title = $request->get('title');
        /**
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $contentEntity
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        $contentEntity = null;
        if ($mode == 'create_node') {
            $contentEntity = new ContentEntity();
            $contentEntity->setTreeParent(null);
            if (!empty($parent)) {
                /** @var Task $parentEntity */
                $parentEntity = $contentRepository->find($parent);
                $contentEntity->setTreeParent($parentEntity);
            }
            $contentEntity->setType('Folder');
            $contentEntity->setSite($this->getSite());
        } elseif ($mode == 'rename_node') {
            $contentEntity = $contentRepository->find($contentId);
            $contentEntity->setModifiedDate(new \DateTime());
        }
        $contentEntity->setTitle($title);
        $contentEntity = $this->cm()->save($contentEntity);
        $return = array();
        $return['id'] = $contentEntity->getId();
        if ($contentEntity->getTreeParent() != null) {
            $return['parent'] = $contentEntity->getTreeParent()->getId();
        } else {
            $return['parent'] = 'Root';
        }
        $return['text'] = $contentEntity->getTitle();
        $return['mode'] = $mode;
        $return['data']['type'] = $contentEntity->getType();
        $return['icon'] = 'glyphicon glyphicon-folder-close';
        $return['children'] = true;
        $return['state'] = array(
            'opened' => false,
            'disabled' => false,
            'selected' => false
        );
        return $this->returnJsonReponse($request, $return);
    }


    /**
     * @Route("/upload.php",name="media_upload")
     * @Method({"POST"})
     */
    public function uploadAction(Request $request)
    {
        $parentId = $request->get('parent', 'Root');
        try {
            $uploadedFile = $request->files->get('file');
            $mediaInfo = $this->mm()->handleUpload($uploadedFile);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
        if (!empty($mediaInfo)) {
            /**
             * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
             * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
             */
            $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
            $content = new ContentEntity();
            $content->setType('Media');
            $content->setSite($this->getSite());
            if ($parentId == 'Root') {
                $content->setTreeParent(null);
            } else {
                $parentEntity = $contentRepository->find($parentId);
                $content->setTreeParent($parentEntity);
            }
            $content->setTitle($mediaInfo['originalName']);
            $content->setMime($mediaInfo['mimeType']);
            $content->setName($mediaInfo['filename']);
            $content->setSize($mediaInfo['size']);
            $content->setExtension($mediaInfo['extension']);
            $this->cm()->save($content);
        }
        return new Response('Ok', 200);
    }

    /**
     * @Route("/delete.php",name="media_delete")
     * @Method({"GET", "POST"})
     */
    public function deleteAction(Request $request)
    {
        $contentId = $request->get('contentId');
        if ($contentId == null) {
            return new Response('Invalid Params', 500);
        }
        /**
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        $content = $contentRepository->find($contentId);
        if ($content == null) {
            return new Response('Content not available', 500);
        }
        if ($this->mm()->deleteMedia($content->getName()) == true) {
            $this->em()->remove($content);
            $this->em()->flush();
        } else {
            return new Response('Media deletion error', 500);
        }
        return $this->returnJsonReponse($request, array('contentId' => $contentId));
    }


    /**
     * @Route("/download.php",name="media_download")
     * @Method({"GET"})
     */
    public function downloadAction(Request $request)
    {
        $contentId = $request->get('contentId');
        /**
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        $content = $contentRepository->find($contentId);
        if ($content == null) {
            return new Response('Content not available', 500);
        }
        $downloadFile = $this->mm()->getFilePath($content->getName(), true);

        $response = new BinaryFileResponse($downloadFile);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $content->getName(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $content->getName())
        );

        return $response;
    }

}
