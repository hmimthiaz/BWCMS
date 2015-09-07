<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\GuidReferenceEntity;
use Symfony\Component\Form\Form;
use AppKernel;

/**
 * Dashboard controller.
 *
 * @Route("/admin")
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
            if (empty($content)) {
                throw new \Exception();
            }
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
        $qb->andWhere(" c.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

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
        $qb->setMaxResults(10000);
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
        $qb->andWhere(" c.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

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
        $qb->setMaxResults(10000);
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

        dump('Done');
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
