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
 * @Route("/admin/navigation")
 */
class NavigationController extends BaseController
{

    /**
     * @Route("/",name="navigation_home")
     * @Template()
     */
    public function indexAction(Request $request)
    {


        $parentId = $request->get('parent', 'Root');

        $qb = $this->cm()->getContentRepository()->getChildrenQueryBuilder(null, false);
        $qb->andWhere(" node.type = 'NavFolder' ");
        $rootFolders = $qb->getQuery()->getResult();

        $jsNodes = array(
            array(
                'id' => 'Root',
                'text' => 'Menus',
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
            'contentTypes' => $this->cm()->getRegisteredNavigation(),
            'jsNodes' => json_encode($jsNodes),
        );

    }

    /**
     * @Route("/data.php",name="navigation_table_data")
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

        $registeredContents = $this->cm()->getRegisteredNavigation();
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            $condition[] = " node.type = '" . $cInfo['type'] . "' ";
        }
        if (!empty($condition)) {
            $qb->andWhere(implode(' OR ', $condition));
        }

        $qb->setFirstResult($start);
        $qb->setMaxResults($length);

        //

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


}
