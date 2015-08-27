<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Classes\Base\ContentTypeInterface;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;
use Bellwether\BWCMSBundle\Entity\ContentRelationEntity;

use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;

use Bellwether\Common\StringUtility;
use Bellwether\Common\Pagination;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\NoResultException;


class ContentQueryService extends BaseService
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return ContentQueryService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * Service Init.
     */
    public function init()
    {
    }


    /**
     * @param ContentEntity $contentEntity
     * @param Pagination $pager
     * @param string $type
     * @return Pagination
     */
    public function getFolderItems($contentEntity = null, Pagination $pager, $type = 'Content', $schema = null)
    {
        $start = $pager->getStart();
        $limit = $pager->getLimit();

        $contentRepository = $this->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder($contentEntity, true);
        $sortOrder = ' ASC';
        if ($contentEntity->getSortOrder() == ContentSortOrderType::DESC) {
            $sortOrder = ' DESC';
        }
        if ($contentEntity->getSortBy() == ContentSortByType::SortIndex) {
            $qb->add('orderBy', 'node.treeLeft' . $sortOrder);
        } elseif ($contentEntity->getSortBy() == ContentSortByType::Created) {
            $qb->add('orderBy', 'node.createdDate' . $sortOrder);
        } elseif ($contentEntity->getSortBy() == ContentSortByType::Published) {
            $qb->add('orderBy', 'node.publishDate' . $sortOrder);
        } elseif ($contentEntity->getSortBy() == ContentSortByType::Title) {
            $qb->add('orderBy', 'node.title' . $sortOrder);
        } elseif ($contentEntity->getSortBy() == ContentSortByType::Size) {
            $qb->add('orderBy', 'node.size' . $sortOrder);
        }

        $registeredContents = $this->cm()->getRegisteredContentTypes($type, $schema);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere(" node.site ='" . $this->sm()->getCurrentSite()->getId() . "' ");
        $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");

        $qb->setFirstResult($start);
        $qb->setMaxResults($limit);

        $pager->callPreQueryCallback($qb, $contentEntity, $type, $schema);

        $result = $qb->getQuery()->getResult();
        $pager->setItems($result);

        $totalCount = $qb->select('COUNT(node)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $pager->setTotalItems($totalCount);

        return $pager;
    }


    /**
     * @param ContentEntity $contentEntity
     * @return array
     */
    public function getContentMenuItems($contentEntity)
    {
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder($contentEntity, false);
        $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
        $result = $qb->getQuery()->getResult();
        return $result;
    }


    /**
     * @param ContentEntity $contentEntity
     * @return array
     */
    public function getContentWidgetItems($contentEntity)
    {
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder($contentEntity, false);
        $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
        $result = $qb->getQuery()->getResult();
        return $result;
    }


    /**
     * @param ContentEntity $contentEntity
     * @return string
     */
    final public function getContentTemplate($contentEntity)
    {
        $templatePath = str_replace('.', DIRECTORY_SEPARATOR, $contentEntity->getType() . '.' . $contentEntity->getSchema());
        return $templatePath . DIRECTORY_SEPARATOR . $contentEntity->getTemplate();
    }

    /**
     * @param ContentEntity $contentEntity
     * @return string|null
     */
    public function getPublicURL($contentEntity)
    {
        $contentClass = $this->cm()->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        return $contentClass->getPublicURL($contentEntity);
    }


    public function getContentBySlugPath($pathSlug = null, $contentTypes = array())
    {
        if ($pathSlug == null) {
            return null;
        }

        $pathList = $this->getCleanedPathArray($pathSlug);
        if (empty($pathList)) {
            return null;
        }

        $content = null;
        foreach ($pathList as $path) {
            $content = $this->getContentBySlug($path, $content, $contentTypes);
            if ($content == null) {
                return null;
            }
        }
        return $content;
    }

    /**
     * @param string $slug
     * @param ContentEntity $parent
     * @return null|ContentEntity
     */
    public function getContentBySlug($slug, $parent = null, $contentTypes = array())
    {
        $qb = $this->cm()->getContentRepository()->createQueryBuilder('node');
        if (!empty($contentTypes)) {
            $condition = array();
            foreach ($contentTypes as $cInfo) {
                $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
            }
            if (!empty($condition)) {
                $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
            }
        }
        $qb->andWhere(" node.slug = '{$slug}' ");
        if (!empty($parent)) {
            $qb->andWhere(" node.treeParent = '" . $parent->getId() . "' ");
        }
        $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
        $qb->setMaxResults(1);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    private function getCleanedPathArray($pathSlug)
    {
        $returnArray = array();
        $pathList = explode('/', $pathSlug);
        if (!empty($pathList)) {
            foreach ($pathList as $path) {
                if (!empty($path)) {
                    $returnArray[] = strtolower($path);
                }
            }
        }
        return $returnArray;
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\ContentRepository
     */
    public function getContentRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:ContentEntity');
    }

}
