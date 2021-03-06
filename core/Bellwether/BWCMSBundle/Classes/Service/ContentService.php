<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentScopeType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;
use Bellwether\BWCMSBundle\Classes\Constants\AuditLevelType;
use Bellwether\BWCMSBundle\Classes\Constants\AuditActionType;

use Bellwether\BWCMSBundle\Entity\ContentRelationEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;

use Bellwether\BWCMSBundle\Classes\Base\ContentTypeInterface;
use Bellwether\BWCMSBundle\Classes\Content\Type\ContentFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\ContentPageType;

use Bellwether\BWCMSBundle\Classes\Content\Type\MediaFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\MediaFileType;

use Bellwether\BWCMSBundle\Classes\Content\Type\NavigationFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\NavigationLinkType;

use Bellwether\BWCMSBundle\Classes\Content\Type\WidgetFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\WidgetHtmlType;

use Bellwether\BWCMSBundle\Classes\Content\Type\TaxonomyCategoryType;
use Bellwether\BWCMSBundle\Classes\Content\Type\TaxonomyTagType;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;
use Bellwether\Common\StringUtility;
use Bellwether\Common\Pagination;
use Bellwether\BWCMSBundle\Classes\Service\ThumbService;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\NoResultException;


class ContentService extends BaseService
{

    private $contentType = array();
    private $contentTypeIcons = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function init()
    {
        if (!$this->loaded) {
            $this->addDefaultContentTypes();
        }
        $this->loaded = true;
    }

    /**
     * @return ContentService
     */
    public function getManager()
    {
        return $this;
    }

    private function addDefaultContentTypes()
    {

        $this->registerContentType(new ContentFolderType($this->container, $this->requestStack));
        $this->registerContentType(new ContentPageType($this->container, $this->requestStack));

        $this->registerContentType(new MediaFolderType($this->container, $this->requestStack));
        $this->registerContentType(new MediaFileType($this->container, $this->requestStack));

        $this->registerContentType(new NavigationFolderType($this->container, $this->requestStack));
        $this->registerContentType(new NavigationLinkType($this->container, $this->requestStack));

        $this->registerContentType(new WidgetFolderType($this->container, $this->requestStack));
        $this->registerContentType(new WidgetHtmlType($this->container, $this->requestStack));

        $this->registerContentType(new TaxonomyCategoryType($this->container, $this->requestStack));
        $this->registerContentType(new TaxonomyTagType($this->container, $this->requestStack));

        //Call other Content Types
        $this->getEventDispatcher()->dispatch('BWCMS.Content.Register');
    }

    /**
     * @param ContentTypeInterface|ContentType $classInstance
     */
    public function registerContentType(ContentTypeInterface $classInstance)
    {
        $slug = $this->getClassSlug($classInstance->getType(), $classInstance->getSchema());
        $this->contentType[$slug] = $classInstance;
    }

    /**
     * @param string $type
     * @param string $schema
     * @return string
     */
    public function getClassSlug($type, $schema)
    {
        return strtoupper($type) . '.' . strtoupper($schema);
    }

    /**
     * @param string $type
     * @param string $schema
     */
    public function removeContentType($type, $schema)
    {
        $slug = $this->getClassSlug($type, $schema);
        if (isset($this->contentType[$slug])) {
            unset($this->contentType[$slug]);
        }
    }


    /**
     * @return array
     */
    public function getAllContentTypes()
    {
        return $this->contentType;
    }

    public function getIndexedContentTypes()
    {
        $returnValue = array();
        $contentTypes = $this->getAllContentTypes();
        if (!empty($contentTypes)) {
            foreach ($contentTypes as $key => $type) {
                if ($type->isIndexed()) {
                    $returnValue[$key] = $type;
                }
            }
        }
        return $returnValue;
    }


    /**
     * @return array
     */
    public function getRegisteredContentTypes($type = 'Content', $schema = null)
    {
        $retVal = array();
        /**
         * @var ContentType $class
         */
        foreach ($this->contentType as $key => $class) {
            if ($class->isType($type, $schema)) {
                $retVal[$key] = array(
                    'name' => $class->getName(),
                    'type' => $class->getType(),
                    'schema' => $class->getSchema(),
                    'isHierarchy' => $class->isHierarchy(),
                    'isTaxonomy' => $class->isTaxonomy(),
                    'isRootItem' => $class->isRootItem(),
                    'class' => $class
                );
            }
        }
        return $retVal;
    }

    public function getMediaContentTypes($type = 'Media')
    {
        $mediaContentTypes = array();
        $registeredContents = $this->getRegisteredContentTypes($type);
        if (!empty($registeredContents)) {
            foreach ($registeredContents as $content) {
                $class = $content['class'];
                if ($class->isUploadEnabled()) {
                    $mediaContentTypes[] = $content;
                }
            }
        }
        return $mediaContentTypes;
    }

    public function getTaxonomyContentTypes($type = 'Taxonomy')
    {
        $mediaContentTypes = array();
        $registeredContents = $this->getRegisteredContentTypes($type);
        if (!empty($registeredContents)) {
            foreach ($registeredContents as $content) {
                $class = $content['class'];
                if ($class->isTaxonomy()) {
                    $mediaContentTypes[] = $content;
                }
            }
        }
        return $mediaContentTypes;
    }

    /**
     * @param string $type
     * @param string $schema
     * @return ContentType
     */
    public function getContentClass($type, $schema = 'Default')
    {
        $slug = $this->getClassSlug($type, $schema);
        if (!isset($this->contentType[$slug])) {
            throw new \RuntimeException("ContentType: `{$slug}` does not exists.");
        }
        return $this->contentType[$slug];
    }

    /**
     * @param ContentEntity $content
     * @param ContentEntity $parentContent
     */
    function cloneContent(ContentEntity $content, ContentEntity $parentContent = null)
    {
        $newContent = clone $content;
        $newContent->setTreeParent($parentContent);
        $parentId = null;
        if ($parentContent != null) {
            $parentId = $parentContent->getId();
        }

        $existingSlug = $newContent->getSlug();
        $newSlug = $this->generateSlug($newContent->getTitle(), $newContent->getType(), $parentId);
        if ($newSlug != $existingSlug) {
            $newContent->setTitle($content->getTitle() . ' Copy');
            $newSlug = $this->generateSlug($newContent->getTitle(), $newContent->getType(), $parentId);
            $newContent->setSlug($newSlug);
        }

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
    }

    /**
     * @param ContentEntity $content
     * @param Form $form
     * @param ContentTypeInterface|BaseContentType $classInstance
     * @return Form|void
     */
    final public function loadFormData(ContentEntity $content = null, Form $form = null, ContentTypeInterface $classInstance)
    {
        if (null === $content) {
            return;
        }
        if (null === $form) {
            return;
        }

        $form->get('id')->setData($content->getId());
        $form->get('type')->setData($content->getType());
        $form->get('schema')->setData($content->getSchema());
        $form->get('scope')->setData($content->getScope());
        $form->get('template')->setData($content->getTemplate());
        $form->get('status')->setData($content->getStatus());
        $form->get('title')->setData($content->getTitle());

        if ($classInstance->isSummaryEnabled()) {
            $form->get('summary')->setData($content->getSummary(true));
        }
        if ($classInstance->isContentEnabled()) {
            $form->get('content')->setData($content->getContent());
        }
        $form->get('slug')->setData($content->getSlug());
        $form->get('sortBy')->setData($content->getSortBy());
        $form->get('sortOrder')->setData($content->getSortOrder());
        if ($classInstance->isPublishDateEnabled()) {
            $form->get('publishDate')->setData($content->getPublishDate());
        }
        if ($classInstance->isExpireDateEnabled()) {
            $form->get('expireDate')->setData($content->getExpireDate());
        }
        if ($classInstance->isEventDateSupported()) {
            $form->get('eventStartDate')->setData($content->getEventStartDate());
            $form->get('eventEndDate')->setData($content->getEventEndDate());
        }

        $taxonomyRelations = $classInstance->getTaxonomyRelations();
        if (!empty($taxonomyRelations)) {
            $existingRelation = $content->getRelation();
            $fieldValues = array();
            foreach ($existingRelation as $relation) {
                if (!isset($taxonomyRelations[$relation->getRelation()]['fieldName'])) {
                    continue;
                }
                $formFieldName = $taxonomyRelations[$relation->getRelation()]['fieldName'];
                $fieldValues[$formFieldName][] = $relation->getRelatedContent()->getId();
            }
            foreach ($taxonomyRelations as $taxonomyRelation) {
                $fieldName = $taxonomyRelation['fieldName'];
                if (isset($fieldValues[$fieldName]) && !empty($fieldValues[$fieldName])) {
                    try {
                        $formField = $form->get($fieldName);
                    } catch (\OutOfBoundsException $e) {
                        continue;
                    }
                    if ($taxonomyRelation['multiple']) {
                        $formField->setData($fieldValues[$fieldName]);
                    } else {
                        $singleValue = array_pop($fieldValues[$fieldName]);
                        if (!is_null($singleValue)) {
                            $formField->setData($singleValue);
                        }
                    }
                }
            }
        }

        $existingMeta = $content->getMeta();
        if (!empty($existingMeta)) {
            /**
             * @var ContentMetaEntity $meta
             */
            foreach ($existingMeta as $meta) {
                $metaField = $meta->getField();
                $metaValue = $meta->getValue();
                $metaType = $meta->getFieldType();
                try {
                    $formField = $form->get($metaField);
                } catch (\OutOfBoundsException $e) {
                    continue;
                }
                $fieldValue = $this->decodeDataFromDB($metaType, $metaField, $metaValue, $classInstance);
                $formField->setData($fieldValue);
            }
        }
        $form = $classInstance->loadFormData($content, $form);
        return $form;
    }

    private function decodeDataFromDB($fieldType, $fieldName, $fieldValue, ContentTypeInterface $classInstance)
    {
        if (is_null($fieldValue)) {
            return null;
        }

        if ($fieldType == ContentFieldType::String || $fieldValue == ContentFieldType::Number) {
            return $fieldValue;
        }
        if ($fieldType == ContentFieldType::Content) {
            return $fieldValue;
        }
        if ($fieldType == ContentFieldType::Date || $fieldType == ContentFieldType::Time || $fieldType == ContentFieldType::DateTime) {
            $dateValue = new \DateTime($fieldValue);
            return $dateValue;
        }
        if ($fieldType == ContentFieldType::Serialized || $fieldType == ContentFieldType::Custom) {
            try {
                $data = $this->getSerializer()->deserialize($fieldValue, 'ArrayCollection', 'json');
            } catch (\RuntimeException $exp) {
                $data = array();
            }
            $data = $this->loadSerializedData($data);
            return $data;
        }
        return $fieldValue;
    }

    /**
     * @param ContentEntity $contentEntity
     * @return array
     */
    public function getContentAllMeta($contentEntity)
    {
        $returnValue = array();
        if (!$contentEntity instanceof ContentEntity) {
            return $returnValue;
        }
        $classInstance = $this->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        $loadedMeta = $contentEntity->getLoadedMeta();
        if (!is_null($loadedMeta)) {
            return $loadedMeta;
        }
        $existingMeta = $contentEntity->getMeta();
        if (!empty($existingMeta)) {
            /**
             * @var ContentMetaEntity $meta
             */
            foreach ($existingMeta as $meta) {
                $metaField = $meta->getField();
                $metaValue = $meta->getValue();
                $metaType = $meta->getFieldType();
                $metaValue = $this->decodeDataFromDB($metaType, $metaField, $metaValue, $classInstance);
                if ($metaType == ContentFieldType::Content) {
                    if (!is_array($metaValue) && !is_null($metaValue)) {
                        $metaValue = $this->getContentRepository()->find($metaValue);
                    }
                }
                if ($metaType == ContentFieldType::Custom) {
                    $metaValue = $classInstance->loadCustomField($metaField, $metaValue);
                }
                $returnValue[$metaField] = $metaValue;
            }
        }
        $contentEntity->setLoadedMeta($returnValue);
        return $returnValue;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param $metaKey
     */
    public function getContentMeta($contentEntity, $metaKey, $default = false)
    {
        $allMeta = $this->getContentAllMeta($contentEntity);
        if (isset($allMeta[$metaKey])) {
            return $allMeta[$metaKey];
        }
        return $default;
    }

    private function loadSerializedData($value)
    {
        usort($value, function ($a, $b) {
            if (isset($a['__sort__']) && isset($b['__sort__'])) {
                return $a['__sort__'] - $b['__sort__'];
            }
            return 0;
        });
        return $value;
    }

    /**
     * @param ContentEntity $content
     * @param array $data
     * @param ContentTypeInterface|BaseContentType $classInstance
     * @return ContentEntity|void
     */
    final public function prepareEntity(ContentEntity $content = null, Form $form = null, ContentTypeInterface $classInstance)
    {
        if (null === $content) {
            return;
        }
        if (null === $form) {
            return;
        }

        $data = $form->getData();
        $fields = $classInstance->getFields();
        foreach ($fields as $fieldName => $fieldInfo) {
            if ($fieldName == 'parent' && !empty($data['parent'])) {
                $parentContent = $this->getContentRepository()->find($data['parent']);
                $content->setTreeParent($parentContent);
            }
            if ($fieldName == 'type') {
                $content->setType($data['type']);
            }
            if ($fieldName == 'schema') {
                $content->setSchema($data['schema']);
            }
            if ($fieldName == 'scope') {
                $content->setScope($data['scope']);
            }
            if ($fieldName == 'status') {
                $content->setStatus($data['status']);
            }
            if ($fieldName == 'template') {
                $content->setTemplate($data['template']);
            }
            if ($fieldName == 'slug') {
                $content->setSlug($data['slug']);
            }
            if ($fieldName == 'title') {
                $content->setTitle($data['title']);
            }
            if ($fieldName == 'summary') {
                $content->setSummary($data['summary']);
            }
            if ($fieldName == 'content') {
                $content->setContent($data['content']);
            }
            if ($fieldName == 'sortBy') {
                $content->setSortBy($data['sortBy']);
            }
            if ($fieldName == 'sortOrder') {
                $content->setSortOrder($data['sortOrder']);
            }
            if ($fieldName == 'publishDate') {
                if ($data['publishDate'] instanceof \DateTime) {
                    $content->setPublishDate($data['publishDate']);
                }
            }
            if ($fieldName == 'expireDate') {
                if ($data['expireDate'] instanceof \DateTime) {
                    $content->setExpireDate($data['expireDate']);
                }
            }
            if ($fieldName == 'eventStartDate') {
                if ($data['eventStartDate'] instanceof \DateTime) {
                    if (!$classInstance->isEventDateTime()) {
                        $data['eventStartDate']->setTime(0, 0, 0);
                    }
                    $content->setEventStartDate($data['eventStartDate']);
                }
            }
            if ($fieldName == 'eventEndDate') {
                if ($data['eventEndDate'] instanceof \DateTime) {
                    if (!$classInstance->isEventDateTime()) {
                        $data['eventEndDate']->setTime(23, 59, 59);
                    }
                    $content->setEventEndDate($data['eventEndDate']);
                }
            }
            if ($fieldName == 'attachment') {
                if ($data['attachment'] instanceof UploadedFile) {
                    $mediaInfo = $this->mm()->handleUpload($data['attachment']);
                    if (!empty($mediaInfo)) {
                        if ($content->getMedia()->count() > 0) {
                            foreach ($content->getMedia() as $mediaToDelete) {
                                $this->em()->remove($mediaToDelete);
                            }
                        }
                        $contentMedia = new ContentMediaEntity();
                        $contentMedia->setFile($mediaInfo['filename']);
                        $contentMedia->setExtension($mediaInfo['extension']);
                        $contentMedia->setMime($mediaInfo['mimeType']);
                        $contentMedia->setSize($mediaInfo['size']);
                        $contentMedia->setHeight($mediaInfo['height']);
                        $contentMedia->setWidth($mediaInfo['width']);
                        if (!is_null($mediaInfo['binary'])) {
                            $contentMedia->setData($mediaInfo['binary']);
                        }
                        $contentMedia->setContent($content);
                        $this->em()->persist($contentMedia);
                    }
                }
            }
        }

        if ($content->getSlug() == null) {
            $parentId = null;
            if ($content->getTreeParent() != null) {
                $parentId = $content->getTreeParent()->getId();
            }
            $content->setSlug($this->generateSlug($content->getTitle(), $content->getType(), $parentId, $content->getId(), $content->getSchema()));
        } else {
            $content->setSlug(StringUtility::sanitizeTitle($content->getSlug()));
        }

        $taxonomyRelations = $classInstance->getTaxonomyRelations();
        if (!empty($taxonomyRelations)) {
            $existingRelation = $content->getRelation();
            $relationsToRemove = array();
            foreach ($existingRelation as $relation) {
                $relationsToRemove[$relation->getId()] = 'Yes';
            }

            foreach ($taxonomyRelations as $taxonomyRelation) {
                $fieldName = $taxonomyRelation['fieldName'];
                if (isset($data[$fieldName])) {
                    $fieldValues = $data[$fieldName];
                    if (!is_array($fieldValues)) {
                        preg_match_all('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $fieldValues, $uuidMatches, PREG_PATTERN_ORDER);
                        $fieldValues = $uuidMatches[0];
                    }
                    if (!empty($fieldValues)) {
                        foreach ($fieldValues as $fieldValue) {
                            $relatedContent = $this->cm()->getContentRepository()->find($fieldValue);
                            if (!empty($relatedContent)) {
                                $relation = $this->getRelationForContent($existingRelation, $taxonomyRelation['name'], $relatedContent);
                                if (is_null($relation)) {
                                    $relation = new ContentRelationEntity();
                                    $relation->setContent($content);
                                    $relation->setRelation($taxonomyRelation['name']);
                                    $relation->setRelatedContent($relatedContent);
                                    $this->em()->persist($relation);
                                } else {
                                    $relationId = $relation->getId();
                                    if (isset($relationsToRemove[$relationId])) {
                                        unset($relationsToRemove[$relationId]);
                                    }
                                }
                            }
                        }
                        unset($data[$fieldName]);
                    }
                }
            }

            foreach ($existingRelation as $relation) {
                $relationId = $relation->getId();
                if (array_key_exists($relationId, $relationsToRemove) === true) {
                    $this->em()->remove($relation);
                }
            }

        }

        $metaData = $this->removeNonMetaData($data);
        if (!empty($metaData)) {
            $existingMeta = $content->getMeta();
            foreach ($metaData as $fieldName => $fieldValue) {
                $meta = $this->getMetaForField($existingMeta, $fieldName);
                $meta->setContent($content);
                $meta->setField($fieldName);
                $meta->setFieldType($fields[$fieldName]['type']);
                if ($fields[$fieldName]['type'] == ContentFieldType::String || $fields[$fieldName]['type'] == ContentFieldType::Number) {
                    $meta->setValue($fieldValue);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Content) {
                    $meta->setValue($fieldValue);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Date) {
                    if ($fieldValue instanceof \DateTime) {
                        $dateString = $fieldValue->format('Y-m-d');
                    } else {
                        $dateString = null;
                    }
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Time) {
                    if ($fieldValue instanceof \DateTime) {
                        $dateString = $fieldValue->format('H:i:sO');
                    } else {
                        $dateString = null;
                    }
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::DateTime) {
                    if ($fieldValue instanceof \DateTime) {
                        $dateString = $fieldValue->format(\DateTime::ISO8601);
                    } else {
                        $dateString = null;
                    }
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Serialized || $fields[$fieldName]['type'] == ContentFieldType::Custom) {
                    $cleanedData = $this->prepareSerializedMeta($fieldValue);
                    $serializedString = $this->getSerializer()->serialize($cleanedData, 'json');
                    $meta->setValue($serializedString);
                }
            }
            foreach ($existingMeta as $meta) {
                if ($meta->getValue() == null) {
                    $this->em()->remove($meta);
                }
            }
        }

        $content = $classInstance->prepareEntity($content, $form);
        return $content;
    }

    private function prepareSerializedMeta($value)
    {
        $returnValue = array();
        $value = $this->cleanArray($value);
        $sort = 0;
        foreach ($value as $key => $value) {
            $value['__sort__'] = $sort++;
            $returnValue[] = $value;
        }
        return $returnValue;
    }

    private function cleanArray($haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->cleanArray($haystack[$key]);
            }
            if (empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }


    /**
     * @param $existingMeta
     * @param $fieldName
     * @return ContentMetaEntity
     */
    private function getMetaForField($existingMeta, $fieldName)
    {
        if (!empty($existingMeta)) {
            foreach ($existingMeta as $eMeta) {
                if ($eMeta->getField() == $fieldName) {
                    return $eMeta;
                }
            }
        }
        $meta = new ContentMetaEntity();
        $this->em()->persist($meta);
        return $meta;
    }

    /**
     * @param array $existingRelation
     * @param string $relation
     * @param ContentEntity $relatedContent
     * @return ContentRelationEntity
     */
    private function getRelationForContent($existingRelation, $relation, $relatedContent)
    {
        if (!empty($existingRelation)) {
            foreach ($existingRelation as $eRelation) {
                if ($eRelation->getRelation() == $relation && $eRelation->getRelatedContent()->getId() == $relatedContent->getId()) {
                    return $eRelation;
                }
            }
        }
        return null;
    }

    /**
     * @param ContentEntity $content
     * @return ContentEntity|void
     */
    public function save(ContentEntity $content = null)
    {
        if (null === $content) {
            return;
        }
        $currentUser = $this->getUser();
        $newRecord = false;
        if ($content->getId() == null) {
            $content->setCreatedDate(new \DateTime());
            if ($content->getAuthor() == null) {
                $content->setAuthor($currentUser);
            }
            $content->setLastModifiedAuthor($currentUser);
            $newRecord = true;
        } else {
            $content->setLastModifiedAuthor($currentUser);
            $content->setModifiedDate(new \DateTime());
        }
        if ($content->getPublishDate() == null && $content->getStatus() == ContentPublishType::Published) {
            $content->setPublishDate(new \DateTime());
        }
        if ($content->getExpireDate() == null && $content->getStatus() == ContentPublishType::Expired) {
            $content->setExpireDate(new \DateTime());
        }
        $this->em()->persist($content);
        $this->em()->flush();
        if ($newRecord) {
            $this->admin()->addAudit(AuditLevelType::Normal, 'Content::' . $content->getType() . '::' . $content->getSchema(), AuditActionType::Add, $content->getId(), 'Added: ' . $content->getTitle());
        } else {
            $this->admin()->addAudit(AuditLevelType::Normal, 'Content::' . $content->getType() . '::' . $content->getSchema(), AuditActionType::Edit, $content->getId(), 'Edit: ' . $content->getTitle());
        }
        return $content;
    }

    /**
     * @param ContentEntity|null $content
     */
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

        $qb = $this->em()->createQueryBuilder();
        $queryResult = $qb->select(array('s3'))
            ->from('BWCMSBundle:S3QueueEntity', 's3')
            ->andWhere($qb->expr()->eq('s3.content', $qb->expr()->literal($content->getId())))
            ->getQuery()
            ->getResult();
        if (!empty($queryResult)) {
            foreach ($queryResult as $deleteItem) {
                $this->em()->remove($deleteItem);
            }
            $this->em()->flush();
        }


        $contentClass = $this->getContentClass($content->getType(), $content->getSchema());
        if ($contentClass->isTaxonomy()) {
            $taxonomyRelations = $this->em()->getRepository('BWCMSBundle:ContentRelationEntity')->findBy(array("relatedContent" => $content));
            if (!empty($taxonomyRelations)) {
                foreach ($taxonomyRelations as $relation) {
                    $this->em()->remove($relation);
                }
            }
        }
        $this->admin()->addAudit(AuditLevelType::Critical, 'Content::' . $content->getType() . '::' . $content->getSchema(), AuditActionType::Delete, $content->getId(), 'Deleted: ' . $content->getTitle());
        $this->em()->remove($content);
        $this->em()->flush();
    }

    public function generateSlug($title, $type = 'Page', $parentId = null, $contentId = null, $schema = null)
    {
        $slug = StringUtility::sanitizeTitle($title);
        while ($this->checkSlugExists($slug, $type, $parentId, $contentId, $schema)) {
            $slug = $slug . '-1';
        }
        return $slug;
    }

    /**
     * @param ContentType $taxonomyClass
     */
    public function getTaxonomyTerms($taxonomyClass, $onlyPublished = false)
    {
        if (!$taxonomyClass->isTaxonomy()) {
            throw new \InvalidArgumentException('Invalid Schema');
        }
        $returnTerms = array();

        if ($taxonomyClass->isHierarchy()) {

            $qb = $this->cm()->getContentRepository()->getChildrenQueryBuilder(null, false);
            $qb->andWhere(" (node.type = '" . $taxonomyClass->getType() . "' AND node.schema = '" . $taxonomyClass->getSchema() . "' )");
            $qb->andWhere(" node.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");
            if ($onlyPublished) {
                $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
            }
            $qb->add('orderBy', 'node.title ASC');

            $rootFolders = $qb->getQuery()->getResult();

            if (!empty($rootFolders)) {
                /** @var ContentEntity $content */
                foreach ($rootFolders as $content) {
                    $node['id'] = $content->getId();
                    $node['text'] = $content->getTitle();
                    $node['icon'] = 'glyphicon glyphicon-folder-open';
                    if ($content->getTreeParent() != null) {
                        $node['parent'] = $content->getTreeParent()->getId();
                    } else {
                        $node['parent'] = '#';
                    }
                    $node['state'] = array(
                        'opened' => true
                    );
                    $returnTerms[] = $node;
                }
            }
        } else {
            /**
             * Get All the root folders
             * @var \Bellwether\BWCMSBundle\Entity\ContentEntity $content
             */
            $contentRepository = $this->cm()->getContentRepository();
            $qb = $contentRepository->getChildrenQueryBuilder(null, true);
            $qb->andWhere(" (node.type = '" . $taxonomyClass->getType() . "' AND node.schema = '" . $taxonomyClass->getSchema() . "' ) ");
            $qb->andWhere(" node.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");
            if ($onlyPublished) {
                $qb->andWhere(" node.status ='" . ContentPublishType::Published . "' ");
            }
            $qb->add('orderBy', 'node.title ASC');

            $entities = $qb->getQuery()->getResult();
            if (!empty($entities)) {
                foreach ($entities as $content) {
                    $returnTerms[$content->getId()] = $content->getTitle();
                }
            }
        }
        return $returnTerms;
    }


    public function checkSlugExists($slug, $type = 'Page', $parentId = null, $contentId = null, $schema = null)
    {

        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder(null, false);
        $qb->andWhere(" node.type = '{$type}' ");
        if (!empty($contentId)) {
            $qb->andWhere(" node.id != '{$contentId}' ");
        }
        if (!empty($schema)) {
            $qb->andWhere(" node.schema = '{$schema}' ");
        }
        $qb->andWhere(" node.slug = '{$slug}' ");
        $qb->andWhere(" node.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

        $qb2 = clone $qb; // don't modify existing query
        $qb2->resetDQLPart('orderBy');
        $qb2->resetDQLPart('having');
        $qb2->select('COUNT(node) AS cnt');
        $countResult = $qb2->getQuery()->setFirstResult(0)->getScalarResult();
        $totalCount = $countResult[0]['cnt'];

        if ($totalCount > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param array $data
     * @return array
     */
    private function removeNonMetaData($data = array())
    {
        $contentFields = $this->getContentFields();
        foreach ($contentFields as $field) {
            unset ($data[$field]);
        }
        return $data;
    }

    public function getContentFields()
    {
        return array(
            "id", "expireDate", "publishDate",
            "title", "summary", "content",
            "slug", "file", "type", "scope",
            "schema", "template", "mime", "extension",
            "size", "height", "width",
            "modifiedDate", "createdDate", "status",
            "eventStartDate", "eventEndDate",
            "author", "site", "parent", "sortBy", "sortOrder");
    }

    /**
     * @return ThumbService
     */
    public function getThumbService()
    {
        return $this->container->get('BWCMS.Thumb');
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\ContentRepository
     */
    public function getContentRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:ContentEntity');
    }

}
