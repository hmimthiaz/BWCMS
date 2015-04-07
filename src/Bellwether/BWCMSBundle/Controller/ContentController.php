<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Bellwether\BWCMSBundle\Entity\ContentEntity;

/**
 * Page controller.
 *
 * @Route("/admin/manage")
 */
class ContentController extends BaseController
{
    /**
     * @Route("/{type}/index.php",name="content_home")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        $type = $request->get('type', 'Content');
        $parentId = $request->get('parent', 'Root');

        $qb = $this->cm()->getContentRepository()->getChildrenQueryBuilder(null, false);

        $registeredContents = $this->cm()->getRegisteredContentTypes($type);
        $mediaContentTypes = $this->cm()->getMediaContentTypes($type);

        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if ($cInfo['isHierarchy']) {
                $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere(" node.site ='" . $this->sm()->getCurrentSite()->getId() . "' ");

        $rootFolders = $qb->getQuery()->getResult();

        $jsNodes = array(
            array(
                'id' => 'Root',
                'text' => 'Folders',
                'icon' => 'glyphicon glyphicon-folder-open',
                'parent' => '#',
                'state' => array(
                    'opened' => true,
                    'selected' => ($parentId == 'Root')
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
                if ($parentId == $content->getId()) {
                    $jsNode['state'] = array(
                        'opened' => true,
                        'selected' => true
                    );
                }
                $jsNodes[] = $jsNode;
            }
        }

        return array(
            'parentId' => $parentId,
            'type' => $type,
            'title' => ucfirst($type) . ' Manager',
            'contentTypes' => $registeredContents,
            'mediaContentTypes' => $mediaContentTypes,
            'jsNodes' => json_encode($jsNodes),
        );
    }

    /**
     * @Route("/browser.php",name="content_browser")
     * @Template()
     */
    public function browserAction(Request $request)
    {

        $parentId = $request->get('parent', 'Root');
        $holder = $request->get('holder', '');

        $qb = $this->cm()->getContentRepository()->getChildrenQueryBuilder(null, false);

        $registeredContents = $this->cm()->getRegisteredContentTypes();

        $qb->andWhere(" node.type = 'Folder' ");
        $qb->andWhere(" node.site ='" . $this->sm()->getCurrentSite()->getId() . "' ");

        $rootFolders = $qb->getQuery()->getResult();

        $jsNodes = array(
            array(
                'id' => 'Root',
                'text' => 'Folders',
                'icon' => 'glyphicon glyphicon-folder-open',
                'parent' => '#',
                'state' => array(
                    'opened' => true,
                    'selected' => ($parentId == 'Root')
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
                if ($parentId == $content->getId()) {
                    $jsNode['state'] = array(
                        'opened' => true,
                        'selected' => true
                    );
                }
                $jsNodes[] = $jsNode;
            }
        }

        return array(
            'parentId' => $parentId,
            'holder' => $holder,
            'contentTypes' => $this->cm()->getRegisteredContentTypes(),
            'jsNodes' => json_encode($jsNodes),
        );


    }


    /**
     * @Route("/create.php",name="content_create")
     * @Template("BWCMSBundle:Content:save.html.twig")
     */
    public function createAction(Request $request)
    {

        $type = $request->get('type', null);
        $schema = $request->get('schema', null);
        $parent = $request->get('parent', null);

        if (is_null($type) || is_null($schema) || is_null($parent)) {
            return $this->returnErrorResponse();
        }

        $class = $this->cm()->getContentClass($type, $schema);
        if ($parent != 'Root') {
            $class->setParent($parent);
        }
        $form = $class->getForm();

        return array(
            'title' => 'Create ' . $class->getName(),
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/edit.php",name="content_edit")
     * @Template("BWCMSBundle:Content:save.html.twig")
     */
    public function editAction(Request $request)
    {
        $contentId = $request->get('contentId');
        if ($contentId == null) {
            return $this->returnErrorResponse();
        }

        /**
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
         */
        $content = $this->cm()->getContentRepository()->find($contentId);

        $class = $this->cm()->getContentClass($content->getType(), $content->getSchema());
        if ($content->getTreeParent() != null) {
            $class->setParent($content->getTreeParent()->getId());
        }
        $form = $class->getForm();
        $form = $this->cm()->loadFormData($content, $form, $class);

        return array(
            'title' => 'Edit ' . $class->getName(),
            'form' => $form->createView()
        );
    }


    /**
     * @Route("/save.php",name="content_save")
     * @Method({"POST"})
     * @Template("BWCMSBundle:Content:save.html.twig")
     */
    public function saveAction(Request $request)
    {

        $contentFormData = $request->request->get('BWCF');
        $type = $contentFormData['type'];
        $schema = $contentFormData['schema'];

        if (is_null($type) || is_null($schema)) {
            return $this->returnErrorResponse();
        }

        $class = $this->cm()->getContentClass($type, $schema);
        $form = $class->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {

            $contentId = $form->get('id')->getData();
            if (empty($contentId)) {
                $contentEntity = new ContentEntity();
                $contentEntity->setSite($this->getSite());
            } else {
                $contentEntity = $this->cm()->getContentRepository()->find($contentId);
            }

            $contentEntity = $this->cm()->prepareEntity($contentEntity, $form, $class);
            $this->cm()->save($contentEntity);

            $parentId = 'Root';
            if ($contentEntity->getTreeParent() != null) {
                $parentId = $contentEntity->getTreeParent()->getId();
            }
            list($type) = explode('.', $contentEntity->getType());
            return $this->redirect($this->generateUrl('content_home', array('type' => $type, 'parent' => $parentId)));
        }

        return array(
            'title' => 'Edit ' . $class->getName(),
            'pageTitle' => 'Edit ' . $class->getName(),
            'form' => $form->createView()
        );
    }


    /**
     * @Route("/upload.php",name="content_media_upload")
     * @Method({"POST"})
     */
    public function uploadAction(Request $request)
    {
        $parentId = $request->get('parent', null);
        $type = $request->get('type', null);
        $schema = $request->get('schema', null);

        if (is_null($type) || is_null($schema) || is_null($parentId)) {
            return $this->returnErrorResponse();
        }

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
            $content->setType($type);
            $content->setSchema($schema);
            $content->setSite($this->getSite());
            if ($parentId == 'Root') {
                $content->setTreeParent(null);
            } else {
                $parentEntity = $contentRepository->find($parentId);
                $content->setTreeParent($parentEntity);
            }
            $content->setTitle($mediaInfo['originalName']);
            $content->setMime($mediaInfo['mimeType']);
            $content->setFile($mediaInfo['filename']);
            $content->setSize($mediaInfo['size']);
            $content->setExtension($mediaInfo['extension']);
            $content->setWidth($mediaInfo['width']);
            $content->setHeight($mediaInfo['height']);

            $content->setSlug($this->cm()->generateSlug($content->getFile(), $content->getType(), $parentId));
            $content->setStatus('Draft');
            $this->cm()->save($content);
        }
        return new Response('Ok', 200);
    }

    /**
     * @Route("/data.php",name="content_table_data")
     * @Method({"GET"})
     */
    public function indexDataAction(Request $request)
    {
        $type = $request->get('type', 'Content');
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
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
         * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $parentFolder
         */
        $uiSortEnabled = false;
        $contentRepository = $this->cm()->getContentRepository();
        if ($parentId == 'Root') {
            $qb = $contentRepository->getChildrenQueryBuilder(null, true);
        } else {
            $parentFolder = $contentRepository->find($parentId);
            $qb = $contentRepository->getChildrenQueryBuilder($parentFolder, true);
            $sortOrder = ' ASC';
            if ($parentFolder->getSortOrder() == ContentSortOrderType::DESC) {
                $sortOrder = ' DESC';
            }
            if ($parentFolder->getSortBy() == ContentSortByType::SortIndex) {
                $uiSortEnabled = true;
                $qb->add('orderBy', 'node.treeLeft' . $sortOrder);
            } elseif ($parentFolder->getSortBy() == ContentSortByType::Created) {
                $qb->add('orderBy', 'node.createdDate' . $sortOrder);
            } elseif ($parentFolder->getSortBy() == ContentSortByType::Published) {
                $qb->add('orderBy', 'node.publishDate' . $sortOrder);
            } elseif ($parentFolder->getSortBy() == ContentSortByType::Title) {
                $qb->add('orderBy', 'node.title' . $sortOrder);
            } elseif ($parentFolder->getSortBy() == ContentSortByType::Size) {
                $qb->add('orderBy', 'node.size' . $sortOrder);
            }
        }

        $registeredContents = $this->cm()->getRegisteredContentTypes($type);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere(" node.site ='" . $this->sm()->getCurrentSite()->getId() . "' ");

        $qb->setFirstResult($start);
        $qb->setMaxResults($length);

        if (!empty($searchString)) {
            $qb->andWhere(" node.title LIKE :query1 OR node.file LIKE :query2 ");
            $qb->setParameter('query1', '%' . $searchString . '%');
            $qb->setParameter('query2', '%' . $searchString . '%');
        }
//
//        var_dump($qb->getDQL());
//        exit;

        $result = $qb->getQuery()->getResult();
        $totalCount = $qb->select('COUNT(node)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $data = array();
        $data['draw'] = $draw;
        $data['sort'] = $uiSortEnabled;

        $data['recordsFiltered'] = $totalCount;
        $data['recordsTotal'] = $totalCount;
        $data['data'] = array();

        if (!empty($result)) {
            foreach ($result as $content) {
                $ca = array();
                $ca['DT_RowId'] = $content->getId();
                $ca['title'] = $content->getTitle();
                $ca['name'] = $content->getFile();
                $ca['type'] = $content->getType();
                $ca['createdDate'] = $content->getCreatedDate()->format('Y-m-d H:i:s');

                $ca['thumbnail'] = $this->cm()->getSystemThumbURL($content, 32, 32);
                $ca['thumbnail'] = '<img class="contentThumb" src="' . $ca['thumbnail'] . '"/>';
                $ca['download'] = '';
                if ($content->getFile() != null) {
                    $ca['download'] = $this->generateUrl('content_media_download', array('contentId' => $content->getId()));
                }
                if ($this->mm()->isImage($content->getFile(), $content->getMime())) {
                    $imageThumb = $this->getImageThumbURL($content->getFile(), 800, 800);
                    $ca['thumbnail'] = '<a href="' . $imageThumb . '" data-title="' . $content->getTitle() . '" class="lightBox">' . $ca['thumbnail'] . '</a>';
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
     * @Route("/sort.php",name="content_sort")
     * @Method({"POST"})
     */
    public function sortAction(Request $request)
    {
        $start = intval($request->get('start', 0));
        $end = intval($request->get('end', 0));
        $contentId = $request->get('contentId');

        $contentRepo = $this->cm()->getContentRepository();
        $content = $contentRepo->find($contentId);
        $toMove = $start - $end;

        try {
            if ($toMove > 0) {
                $contentRepo->moveUp($content, $toMove);
            }
            if ($toMove < 0) {
                $contentRepo->moveDown($content, $toMove * -1);
            }
        } catch (\Gedmo\Exception\InvalidArgumentException $exp) {
            return $this->returnErrorResponse($exp->getMessage());
        }
        return $this->returnJsonReponse($request, array());
    }

    /**
     * @Route("/delete.php",name="content_delete")
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
        if ($content->getFile() == null) {
            $this->em()->remove($content);
            $this->em()->flush();
        } else {
            if ($this->mm()->deleteMedia($content->getFile()) == true) {
                $this->em()->remove($content);
                $this->em()->flush();
            } else {
                return new Response('Media deletion error', 500);
            }
        }

        return $this->returnJsonReponse($request, array('contentId' => $contentId));
    }

    /**
     * @Route("/download.php",name="content_media_download")
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
        $downloadFile = $this->mm()->getFilePath($content->getFile(), true);

        $response = new BinaryFileResponse($downloadFile);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $content->getFile(),
            iconv('UTF-8', 'ASCII//TRANSLIT', $content->getFile())
        );

        return $response;
    }

}
