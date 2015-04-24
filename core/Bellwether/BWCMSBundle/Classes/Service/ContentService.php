<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;

use Bellwether\BWCMSBundle\Classes\Content\ContentTypeInterface;
use Bellwether\BWCMSBundle\Classes\Content\Type\ContentFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\ContentPageType;
use Bellwether\BWCMSBundle\Classes\Content\Type\ContentArticleType;
use Bellwether\BWCMSBundle\Classes\Content\Type\MediaFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\MediaFileType;
use Bellwether\BWCMSBundle\Classes\Content\Type\NavigationFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\NavigationLinkType;
use Bellwether\BWCMSBundle\Classes\Content\Type\WidgetFolderType;
use Bellwether\BWCMSBundle\Classes\Content\Type\WidgetHtmlType;


use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

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
        $this->registerContentType(new ContentArticleType($this->container, $this->requestStack));

        $this->registerContentType(new MediaFolderType($this->container, $this->requestStack));
        $this->registerContentType(new MediaFileType($this->container, $this->requestStack));

        $this->registerContentType(new NavigationFolderType($this->container, $this->requestStack));
        $this->registerContentType(new NavigationLinkType($this->container, $this->requestStack));

        $this->registerContentType(new WidgetFolderType($this->container, $this->requestStack));
        $this->registerContentType(new WidgetHtmlType($this->container, $this->requestStack));

        //Call other Content Types
        $this->getEventDispatcher()->dispatch('BWCMS.Content.Register');
    }

    /**
     * @param ContentTypeInterface|ContentType $classInstance
     */
    public function registerContentType(ContentTypeInterface $classInstance)
    {
        $slug = $classInstance->getType() . '.' . $classInstance->getSchema();
        $this->contentType[$slug] = $classInstance;
    }

    /**
     * @return array
     */
    public function getAllContentTypes()
    {
        return $this->contentType;
    }

    /**
     * @return array
     */
    public function getRegisteredContentTypes($type = 'Content')
    {
        $retVal = array();
        /**
         * @var ContentType $class
         */
        foreach ($this->contentType as $key => $class) {
            if ($class->isType($type)) {
                $retVal[$key] = array(
                    'name' => $class->getName(),
                    'type' => $class->getType(),
                    'schema' => $class->getSchema(),
                    'isHierarchy' => $class->isHierarchy(),
                    'isRootItem' => $class->isRootItem(),
                    'class' => $class
                );
            }
        }
        return $retVal;
    }

    public function getMediaContentTypes($type = 'Content')
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

    /**
     * @param string $type
     * @param string $schema
     * @return ContentType
     */
    public function getContentClass($type, $schema = 'Default')
    {
        $slug = $type . '.' . $schema;
        if (!isset($this->contentType[$slug])) {
            throw new \RuntimeException("ContentType: `{$slug}` does not exists.");
        }
        return $this->contentType[$slug];
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
        $form->get('template')->setData($content->getTemplate());
        $form->get('status')->setData($content->getStatus());
        $form->get('title')->setData($content->getTitle());

        if ($classInstance->isSummaryEnabled()) {
            $form->get('summary')->setData($content->getSummary());
        }
        if ($classInstance->isContentEnabled()) {
            $form->get('content')->setData($content->getContent());
        }
        if ($classInstance->isSlugEnabled()) {
            $form->get('slug')->setData($content->getSlug());
        }
        if ($classInstance->isSortEnabled()) {
            $form->get('sortBy')->setData($content->getSortBy());
            $form->get('sortOrder')->setData($content->getSortOrder());
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
                $fieldValue = $this->decodeDataFromDB($metaType, $metaValue, $classInstance);
                $formField->setData($fieldValue);
            }
        }
        $form = $classInstance->loadFormData($content, $form);
        return $form;
    }

    private function decodeDataFromDB($fieldType, $fieldValue, ContentTypeInterface $classInstance)
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
        if ($fieldType == ContentFieldType::Serialized) {
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
        $classInstance = $this->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        $existingMeta = $contentEntity->getMeta();
        if (!empty($existingMeta)) {
            /**
             * @var ContentMetaEntity $meta
             */
            foreach ($existingMeta as $meta) {
                $metaField = $meta->getField();
                $metaValue = $meta->getValue();
                $metaType = $meta->getFieldType();
                $metaValue = $this->decodeDataFromDB($metaType, $metaValue, $classInstance);
                if ($metaType == ContentFieldType::Content) {
                    if (!is_array($metaValue) && !is_null($metaValue)) {
                        $metaValue = $this->getContentRepository()->find($metaValue);
                    }
                }
                $returnValue[$metaField] = $metaValue;
            }
        }
        return $returnValue;
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

        $this->dump($data);

        $fields = $classInstance->getFields();

        foreach ($fields as $fieldName => $fieldInfo) {
            if (!isset($data[$fieldName]) || empty($data[$fieldName])) {
                continue;
            }
            if ($fieldName == 'parent') {
                $parentContent = $this->getContentRepository()->find($data['parent']);
                $content->setTreeParent($parentContent);
            }
            if ($fieldName == 'type') {
                $content->setType($data['type']);
            }
            if ($fieldName == 'schema') {
                $content->setSchema($data['schema']);
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
            if ($fieldName == 'attachment') {
                $mediaInfo = $this->mm()->handleUpload($data['attachment']);
                $content->setMime($mediaInfo['mimeType']);
                $content->setFile($mediaInfo['filename']);
                $content->setSize($mediaInfo['size']);
                $content->setExtension($mediaInfo['extension']);
                $content->setWidth($mediaInfo['width']);
                $content->setHeight($mediaInfo['height']);
            }
        }
        if ($content->getSlug() == null) {
            $parentId = null;
            if ($content->getTreeParent() != null) {
                $parentId = $content->getTreeParent()->getId();
            }
            $content->setSlug($this->generateSlug($content->getTitle(), $content->getType(), $parentId, $content->getId()));
        } else {
            $content->setSlug($this->sanitizeTitle($content->getSlug()));
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
                    $dateString = $fieldValue->format('Y-m-d');
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Time) {
                    $dateString = $fieldValue->format('H:i:sO');
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::DateTime) {
                    $dateString = $fieldValue->format(\DateTime::ISO8601);
                    $meta->setValue($dateString);
                }
                if ($fields[$fieldName]['type'] == ContentFieldType::Serialized) {
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
     * @param ContentEntity $content
     * @return ContentEntity|void
     */
    public function save(ContentEntity $content = null)
    {
        if (null === $content) {
            return;
        }

        if ($content->getId() == null) {
            $content->setCreatedDate(new \DateTime());
        }
        $content->setModifiedDate(new \DateTime());
        if ($content->getAuthor() == null) {
            $content->setAuthor($this->getUser());
        }
        $this->em()->persist($content);
        $this->em()->flush();
        return $content;
    }

    public function generateSlug($title, $type = 'Page', $parentId = null, $contentId = null)
    {
        $slug = $this->sanitizeTitle($title);
        while ($this->checkSlugExists($slug, $type, $parentId, $contentId)) {
            $slug = $slug + '-1';
        }
        return $slug;
    }

    public function getContentBySlugPath($pathSlug = null)
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
            $content = $this->getContentBySlug($path, $content);
            if ($content == null) {
                return null;
            }
        }
        return $content;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param string $type
     * @param int $start
     */
    public function getFolderItems($contentEntity = null, $start = 0, $type = 'Content')
    {
        $limit = 5;

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
        $qb->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        $totalCount = $qb->select('COUNT(node)')->setFirstResult(0)->getQuery()->getSingleScalarResult();

        return array(
            'start' => $start,
            'limit' => $limit,
            'items' => $result,
            'count' => $totalCount
        );
    }

    /**
     * @param ContentEntity $slug
     * @return array
     */
    public function getContentMenuItemsBySlug($contentEntity)
    {
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder($contentEntity, false);
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

    /**
     * @param string $slug
     * @param ContentEntity $parent
     * @return null|ContentEntity
     */
    private function getContentBySlug($slug, $parent = null)
    {
        $criteria = array(
            'slug' => $slug
        );
        if (!empty($parent)) {
            $criteria['treeParent'] = $parent->getId();
        }
        return $this->getContentRepository()->findOneBy($criteria);
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

    public function checkSlugExists($slug, $type = 'Page', $parentId = null, $contentId = null)
    {
        $contentRepository = $this->cm()->getContentRepository();
        if (empty($parentId) || $parentId == 'Root') {
            $qb = $contentRepository->getChildrenQueryBuilder(null, true);
        } else {
            $parentFolder = $contentRepository->find($parentId);
            $qb = $contentRepository->getChildrenQueryBuilder($parentFolder, true);
        }
        $qb->andWhere(" node.type = '{$type}' ");
        if (!empty($contentId)) {
            $qb->andWhere(" node.id != '{$contentId}' ");
        }
        $qb->andWhere(" node.slug = '{$slug}' ");
        $totalCount = $qb->select('COUNT(node)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
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
            "slug", "file", "type",
            "schema", "template", "mime", "extension",
            "size", "height", "width",
            "modifiedDate", "createdDate", "status",
            "author", "site", "parent");
    }

    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\ContentRepository
     */
    public function getContentRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:ContentEntity');
    }


    public function getSystemThumbURL(ContentEntity $content = null, $width, $height)
    {
        $contentClass = $this->getContentClass($content->getType(), $content->getSchema());
        if (!$contentClass->isUploadEnabled()) {
            $thumbURL = $this->getThumbService()
                ->open($contentClass->getImage())
                ->resize($width, $height)
                ->cacheFile('guess');
            return $thumbURL;
        }
        return $this->mm()->getSystemThumbURL($content->getFile(), $content->getMime(), $content->getExtension(), $width, $height);
    }

    private function getContentTypeResourceImage($type)
    {
        if (in_array($type, $this->getContentTypeIcons())) {
            return '@BWCMSBundle/Resources/icons/content/' . $type . '.png';
        }
        return '@BWCMSBundle/Resources/icons/content/Unknown.png';
    }

    private function getContentTypeIcons()
    {
        if ($this->contentTypeIcons == null) {
            $this->contentTypeIcons = array();
            /**
             * @var \Symfony\Component\HttpKernel\Config\FileLocator $fileLocator
             * @var \Symfony\Component\Finder\SplFileInfo $file
             */
            $fileLocator = $this->container->get('file_locator');
            $iconLocation = $fileLocator->locate('@BWCMSBundle/Resources/icons/content');
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in($iconLocation);
            foreach ($finder as $file) {
                $this->contentTypeIcons[] = $file->getBasename('.' . $file->getExtension());
            }
        }
        return $this->contentTypeIcons;
    }

    public static function sanitizeTitle($title)
    {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        $mbStrLen = mb_strlen($title, 'utf-8');
        $strLen = strlen($title);
        if ($mbStrLen == $strLen) {
            $cleaned = strtolower($title);
        } else {
            $cleaned = urlencode(mb_strtolower($title));
        }

        $title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '-', $title);

        // Convert nbsp, ndash and mdash to hyphens
        $title = str_replace(array('%c2%a0', '%e2%80%93', '%e2%80%94'), '-', $title);

        // Strip these characters entirely
        $title = str_replace(array(
            // iexcl and iquest
            '%c2%a1', '%c2%bf',
            // angle quotes
            '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
            // curly quotes
            '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
            '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
            // copy, reg, deg, hellip and trade
            '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
            // acute accents
            '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
            // grave accent, macron, caron
            '%cc%80', '%cc%84', '%cc%8c',
        ), '', $title);

        // Convert times to x
        $title = str_replace('%c3%97', 'x', $title);

        $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');

        return $title;
    }

}