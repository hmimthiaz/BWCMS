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
use Bellwether\BWCMSBundle\Entity\ContentEntity;

/**
 * Page controller.
 *
 * @Route("/admin/content")
 */
class ContentController extends BaseController
{
    /**
     * @Route("/",name="content_home")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        $parentId = $request->get('parent', 'Root');

        $qb = $this->cm()->getContentRepository()->getChildrenQueryBuilder(null, false);
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
            'contentTypes' => $this->cm()->getRegisteredContent(),
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
            'contentTypes' => $this->cm()->getRegisteredContent(),
            'jsNodes' => json_encode($jsNodes),
        );


    }


    /**
     * @Route("/create.php",name="content_create")
     * @Template("BWCMSBundle:Content:save.html.twig")
     */
    public function createAction(Request $request)
    {

        $type = $request->get('type', 'Page');
        $schema = $request->get('schema', 'Default');
        $parent = $request->get('parent', 'Root');

        $class = $this->cm()->getContentClass($type, $schema);
        if ($parent != 'Root') {
            $class->setParent($parent);
        }
        $form = $class->getForm();

        $title = '';
        if ($class->isIsContent()) {
            $title = 'Content: ';
        }
        if ($class->isIsNavigation()) {
            $title = 'Navigation: ';
        }
        return array(
            'title' => $title . 'Create ' . $class->getName(),
            'pageTitle' => $title . 'Create ' . $class->getName(),
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

        $title = '';
        if ($class->isIsContent()) {
            $title = 'Content: ';
        }
        if ($class->isIsNavigation()) {
            $title = 'Navigation: ';
        }
        return array(
            'title' => $title . 'Edit ' . $class->getName(),
            'pageTitle' => $title . 'Edit ' . $class->getName(),
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

        if ($type == null || $schema == null) {
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

            $contentEntity = $this->cm()->prepareEntity($contentEntity, $form->getData(), $class);
            $this->cm()->save($contentEntity);

            $parentId = 'Root';
            if ($contentEntity->getTreeParent() != null) {
                $parentId = $contentEntity->getTreeParent()->getId();
            }
            if ($class->isIsNavigation()) {
                return $this->redirect($this->generateUrl('navigation_home', array('parent' => $parentId)));
            }
            return $this->redirect($this->generateUrl('content_home', array('parent' => $parentId)));
        }

        $title = '';
        if ($class->isIsContent()) {
            $title = 'Content: ';
        }
        if ($class->isIsNavigation()) {
            $title = 'Navigation: ';
        }
        return array(
            'title' => $title . 'Edit ' . $class->getName(),
            'pageTitle' => $title . 'Edit ' . $class->getName(),
            'form' => $form->createView()
        );
    }


    /**
     * @Route("/data.php",name="content_table_data")
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

        $registeredContents = $this->cm()->getRegisteredContent();
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            $condition[] = " node.type = '" . $cInfo['type'] . "' ";
        }
        if (!empty($condition)) {
            $qb->andWhere(implode(' OR ', $condition));
        }

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
                $ca['createdDate'] = $content->getCreatedDate()->format('Y-m-d H:i:s');;
                $ca['thumbnail'] = $this->cm()->getSystemThumbURL($content, 32, 32);
                $ca['thumbnail'] = '<img class="contentThumb" src="' . $ca['thumbnail'] . '"/>';
                $data['data'][] = $ca;
            }
        }
        return $this->returnJsonReponse($request, $data);
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

}
