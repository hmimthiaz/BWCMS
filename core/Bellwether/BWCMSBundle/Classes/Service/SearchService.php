<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\SearchEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;


class SearchService extends BaseService
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SearchService
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
        if (!$this->loaded) {

        }
        $this->loaded = true;
    }

    public function runIndex()
    {
        $indexContentTypes = $this->cm()->getIndexedContentTypes();
        if (empty($indexContentTypes)) {
            return;
        }

        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->createQueryBuilder('node');
        $qb->add('orderBy', 'node.modifiedDate ASC');
        /**
         * @var ContentType $contentType
         */
        $condition = array();
        foreach ($indexContentTypes as $contentType) {
            $condition[] = " (node.type = '" . $contentType->getType() . "' AND node.schema = '" . $contentType->getSchema() . "' )";
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
        $qb->andWhere($qb->expr()->gt('node.modifiedDate', ':date_modified'));
        $qb->setParameter('date_modified', $this->getLastIndexedDate(), \Doctrine\DBAL\Types\Type::DATETIME);
        $qb->setFirstResult(0);
        $qb->setMaxResults(3);
        $result = $qb->getQuery()->getResult();
        $lastContentModifiedDate = new \DateTime();

        if (!empty($result)) {
            /**
             * @var ContentEntity $content
             */
            foreach ($result as $content) {
                $this->indexContent($content);
                $lastContentModifiedDate = $content->getModifiedDate();
            }
        }
        $this->saveLastIndexDate($lastContentModifiedDate);
    }

    public function indexContent(ContentEntity $content = null)
    {

    }

    public function getLastIndexedDate()
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            return new \DateTime('@0');
        }
        return new \DateTime($preferenceEntity->getValue());
    }

    public function saveLastIndexDate(\DateTime $dateTime)
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            $preferenceEntity = new PreferenceEntity();
            $preferenceEntity->setField('_SEARCH_LIT_');
            $preferenceEntity->setType('_SEARCH_');
            $preferenceEntity->setFieldType(PreferenceFieldType::Internal);
            $preferenceEntity->setSite(null);
        }
        $dateString = $dateTime->format(\DateTime::ISO8601);
        $preferenceEntity->setValue($dateString);
        $this->em()->persist($preferenceEntity);
        $this->em()->flush();
        return true;
    }

}
