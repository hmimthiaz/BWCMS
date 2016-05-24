<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;
use Bellwether\BWCMSBundle\Entity\GuidReferenceEntity;
use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Symfony\Component\Form\Form;
use AppKernel;

/**
 * Dashboard controller.
 *
 * @Route("/admin")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class SiteCopyController extends BaseController implements BackEndControllerInterface
{

    /**
     * @var SiteEntity|null
     */
    private $sourceSite = null;
    private $sourceSiteID = null;
    /**
     * @var SiteEntity|null
     */
    private $targetSite = null;
    private $targetSiteID = null;

    private $loadedReference = array();


    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->sourceSite = $this->sm()->getSiteBySlug('en');
        $this->sourceSiteID = $this->sourceSite->getId();
        $this->targetSite = $this->sm()->getSiteBySlug('ar');
        $this->targetSiteID = $this->targetSite->getId();
    }

    function createGUIDReference($sourceID, $targetID, $createdDate, $type = 'Content')
    {
        $reference = new GuidReferenceEntity();

        $reference->setType($type);
        $reference->setSourceSiteGUID($this->sourceSiteID);
        $reference->setTargetSiteGUID($this->targetSiteID);
        $reference->setSourceGUID($sourceID);
        $reference->setTargetGUID($targetID);
        $reference->setCreatedDate($createdDate);

        $this->em()->persist($reference);
        $this->em()->flush();
    }

    /**
     * @param $sourceID
     * @return null|ContentEntity
     */
    function getTargetContent($sourceID)
    {
        $loadedReferenceKey = $this->sourceSiteID . $this->targetSiteID . $sourceID;
        if (isset($this->loadedReference[$loadedReferenceKey])) {
            return $this->loadedReference[$loadedReferenceKey];
        }

        $content = null;
        $criteria = array(
            'sourceSiteGUID' => $this->sourceSiteID,
            'targetSiteGUID' => $this->targetSiteID,
            'sourceGUID' => $sourceID,
            'type' => 'Content',
        );
        /**
         * @var GuidReferenceEntity $reference
         */
        $reference = $this->em()->getRepository('BWCMSBundle:GuidReferenceEntity')->findOneBy($criteria);
        if (!empty($reference)) {
            $content = $this->cm()->getContentRepository()->find($reference->getTargetGUID());
            $this->loadedReference[$loadedReferenceKey] = $content;
        }
        return $content;
    }

    /**
     * @Route("/sitecopy/start.php",name="_bwcms_admin_sitecopy_start")
     * @Template()
     */
    public function resetAction()
    {
        $this->saveLastCreatedDate(null);
        return $this->redirectToRoute('_bwcms_admin_sitecopy_delete');
    }

    /**
     * @Route("/sitecopy/delete.php",name="_bwcms_admin_sitecopy_delete")
     * @Template()
     */
    public function deleteTargetAction()
    {
        set_time_limit(0);

        $connection = $this->em()->getConnection();
        $platform   = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('BWGuidReference', true /* whether to cascade */));

        $prefRepository = $this->em()->getRepository('BWCMSBundle:PreferenceEntity');
        $qb = $prefRepository->createQueryBuilder('p');
        $qb->andWhere(" p.site ='" . $this->targetSiteID . "' ");
        $qb->add('orderBy', 'p.id ASC');
        $qb->setMaxResults(9999);

        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var PreferenceEntity $pref ;
             */
            foreach ($result as $pref) {
                $this->em()->remove($pref);
                $this->em()->flush();
            }
        }

        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->createQueryBuilder('c');
        $qb->andWhere(" c.site ='" . $this->targetSiteID . "' ");
        $registeredContents = $this->cm()->getRegisteredContentTypes(null, null);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if (!$cInfo['isHierarchy']) {
                $condition[] = " (c.type = '" . $cInfo['type'] . "' AND c.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->add('orderBy', 'c.createdDate DESC');
        $qb->setMaxResults(9999);
        $result = $qb->getQuery()->getResult();
        if (!empty($result)) {
            /**
             * @var ContentEntity $content ;
             */
            foreach ($result as $content) {
                $this->delete($content);
            }
        }

        $qb = $contentRepository->createQueryBuilder('c');
        $qb->andWhere(" c.site ='" . $this->targetSiteID . "' ");
        $registeredContents = $this->cm()->getRegisteredContentTypes(null, null);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if ($cInfo['isHierarchy']) {
                $condition[] = " (c.type = '" . $cInfo['type'] . "' AND c.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->add('orderBy', 'c.createdDate DESC');
        $qb->setMaxResults(9999);
        $result = $qb->getQuery()->getResult();
        if (!empty($result)) {
            /**
             * @var ContentEntity $content ;
             */
            foreach ($result as $content) {
                $this->delete($content);
            }
        }
        return $this->redirectToRoute('_bwcms_admin_sitecopy_folders');
    }

    /**
     * @Route("/sitecopy/folders.php",name="_bwcms_admin_sitecopy_folders")
     * @Template()
     */
    public function copyFoldersAction()
    {
        set_time_limit(0);
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->createQueryBuilder('c');
        $qb->andWhere(" c.site ='" . $this->sourceSiteID . "' ");

        $registeredContents = $this->cm()->getRegisteredContentTypes(null, null);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if ($cInfo['isHierarchy']) {
                $condition[] = " (c.type = '" . $cInfo['type'] . "' AND c.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->add('orderBy', 'c.createdDate ASC');
        $qb->setMaxResults(9999);
        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var ContentEntity $content ;
             */
            foreach ($result as $content) {
                $contentParent = null;
                if ($content->getTreeParent() != null) {
                    $contentParent = $this->getTargetContent($content->getTreeParent()->getId());
                }
                $newContent = $this->cloneContent($content, $contentParent);
                $this->createGUIDReference($content->getId(), $newContent->getId(), $content->getCreatedDate());
            }
        }
        return $this->redirectToRoute('_bwcms_admin_sitecopy_items');
    }

    /**
     * @Route("/sitecopy/content.php",name="_bwcms_admin_sitecopy_items")
     * @Template()
     */
    function copyItemsAction()
    {
        set_time_limit(0);
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->createQueryBuilder('c');
        $qb->andWhere(" c.site ='" . $this->sourceSiteID . "' ");

        $registeredContents = $this->cm()->getRegisteredContentTypes(null, null);
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if (!$cInfo['isHierarchy']) {
                $condition[] = " (c.type = '" . $cInfo['type'] . "' AND c.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->add('orderBy', 'c.createdDate ASC');
        $qb->setMaxResults(9999);
        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var ContentEntity $content ;
             */
            foreach ($result as $content) {
                $contentParent = null;
                if ($content->getTreeParent() != null) {
                    $contentParent = $this->getTargetContent($content->getTreeParent()->getId());
                }
                $newContent = $this->cloneContent($content, $contentParent);
                $this->createGUIDReference($content->getId(), $newContent->getId(), $content->getCreatedDate());
            }
        }
        return $this->redirectToRoute('_bwcms_admin_sitecopy_fixmeta');

    }

    /**
     * @Route("/sitecopy/fixmeta.php",name="_bwcms_admin_sitecopy_fixmeta")
     * @Template()
     */
    function fixMetaAction()
    {
        set_time_limit(0);
        $contentMetaRepository = $this->em()->getRepository('BWCMSBundle:ContentMetaEntity');
        $qb = $contentMetaRepository->createQueryBuilder('m');
        $qb->leftJoin('m.content', 'c');
        $qb->andWhere(" c.site ='" . $this->targetSiteID . "' ");
        $qb->add('orderBy', 'c.createdDate ASC');
        $qb->setMaxResults(9999);
        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var ContentMetaEntity $meta ;
             */
            foreach ($result as $meta) {
                $matches = null;
                $searchedValue = $meta->getValue();
                preg_match_all('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $searchedValue, $matches);
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $contentId) {
                        $newContent = $this->getTargetContent($contentId);
                        if (!empty($newContent)) {
                            $newContentId = $newContent->getId();
                            $searchedValue = str_replace($contentId, $newContentId, $searchedValue);
                        }
                    }
                    $meta->setValue($searchedValue);
                    $this->em()->persist($meta);
                    $this->em()->flush();
                }
            }
        }

        return $this->redirectToRoute('_bwcms_admin_sitecopy_pref');
    }


    /**
     * @Route("/sitecopy/pref.php",name="_bwcms_admin_sitecopy_pref")
     * @Template()
     */
    function copyPrefAction()
    {
        set_time_limit(0);
        $prefRepository = $this->em()->getRepository('BWCMSBundle:PreferenceEntity');
        $qb = $prefRepository->createQueryBuilder('p');
        $qb->andWhere(" p.site ='" . $this->sourceSiteID . "' ");
        $qb->add('orderBy', 'p.id ASC');
        $qb->setMaxResults(9999);

        $result = $qb->getQuery()->getResult();

        if (!empty($result)) {
            /**
             * @var PreferenceEntity $pref ;
             */
            foreach ($result as $pref) {
                $newPref = clone $pref;
                $newPref->setSite($this->targetSite);

                $matches = null;
                $searchedValue = $pref->getValue();
                preg_match_all('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $searchedValue, $matches);
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $contentId) {
                        $newContent = $this->getTargetContent($contentId);
                        if (!empty($newContent)) {
                            $newContentId = $newContent->getId();
                            $searchedValue = str_replace($contentId, $newContentId, $searchedValue);
                        }
                    }
                    $newPref->setValue($searchedValue);
                }
                $this->em()->persist($newPref);
                $this->em()->flush();
            }
        }
        print 'done';
        exit;
    }


    /**
     * @param ContentEntity $content
     * @param ContentEntity|null $parentContent
     * @return ContentEntity
     */
    function cloneContent(ContentEntity $content, ContentEntity $parentContent = null)
    {
        $newContent = clone $content;
        //$newContent->setTitle('[' . $this->targetSite->getSlug() . '] - ' . $content->getTitle());
        $newContent->setTreeParent($parentContent);
        $newContent->setSite($this->targetSite);
        $parentId = null;

        $this->em()->persist($newContent);

        $existingMedia = $content->getMedia();
        if (!empty($existingMedia)) {
            foreach ($existingMedia as $media) {
                $newMedia = clone $media;
                $newMedia->setContent($newContent);
                $newContent->addMedia($newMedia);
                $this->em()->persist($newMedia);
            }
        }

        $existingRelation = $content->getRelation();
        if (!empty($existingRelation)) {
            foreach ($existingRelation as $relation) {
                $newRelation = clone $relation;
                $newRelation->setContent($newContent);
                $newContent->addRelation($newRelation);
                $this->em()->persist($newRelation);
            }
        }

        $existingMeta = $content->getMeta();
        if (!empty($existingMeta)) {
            foreach ($existingMeta as $meta) {
                $newMeta = clone $meta;
                $newMeta->setContent($newContent);
                $newContent->addMeta($newMeta);
                $this->em()->persist($newMeta);
            }
        }
        $this->em()->flush();

        return $newContent;
    }

    public function delete(ContentEntity $content = null)
    {
        $existingMeta = $content->getMeta();
        if (!empty($existingMeta)) {
            foreach ($existingMeta as $meta) {
                $this->em()->remove($meta);
            }
        }
        $existingMedia = $content->getMedia();
        if (!empty($existingMedia)) {
            foreach ($existingMedia as $media) {
                $this->em()->remove($media);
            }
        }
        $existingRelation = $content->getRelation();
        if (!empty($existingRelation)) {
            foreach ($existingRelation as $relation) {
                $this->em()->remove($relation);
            }
        }
        $searchEntity = $this->search()->searchIndexEntity($content);
        if (!empty($searchEntity)) {
            $this->em()->remove($searchEntity);
        }
        $contentClass = $this->cm()->getContentClass($content->getType(), $content->getSchema());
        if ($contentClass->isTaxonomy()) {
            $taxonomyRelations = $this->em()->getRepository('BWCMSBundle:ContentRelationEntity')->findBy(array("relatedContent" => $content));
            if (!empty($taxonomyRelations)) {
                foreach ($taxonomyRelations as $relation) {
                    $this->em()->remove($relation);
                }
            }
        }
        $this->em()->remove($content);
        $this->em()->flush();
    }


    function getLastCreatedDate()
    {
        $lastCreatedDate = $this->pref()->getSinglePreference('__CONTENT_COPY_LAST_CREATED_DATE__');
        if (!empty($lastCreatedDate)) {
            $lastCreatedDate = new \DateTime($lastCreatedDate);
        }
        return $lastCreatedDate;
    }

    /**
     * @param \DateTime $lastCreatedDate
     */
    function saveLastCreatedDate($lastCreatedDate = null)
    {
        if ($lastCreatedDate != null) {
            $lastCreatedDate = $lastCreatedDate->format(\DateTime::ISO8601);
        }
        $this->pref()->saveSinglePreference('__CONTENT_COPY_LAST_CREATED_DATE__', $lastCreatedDate);
    }

    /**
     * @return \Bellwether\BWCMSBundle\Entity\ContentRepository
     */
    public function getContentRepository()
    {

    }


}
