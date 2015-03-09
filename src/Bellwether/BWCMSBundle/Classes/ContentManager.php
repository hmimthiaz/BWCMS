<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Classes\Content\ContentTypeInterface;
use Bellwether\BWCMSBundle\Classes\Content\Type\FolderContentType;
use Bellwether\BWCMSBundle\Classes\Content\Type\MediaContentType;
use Bellwether\BWCMSBundle\Classes\Content\Type\PageContentType;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;


class ContentManager extends BaseService
{

    private $contentType = array();
    private $contentTypeIcons = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
        $this->addDefaultContentTypes();
    }

    /**
     * @return ContentManager
     */
    public function getManager()
    {
        return $this;
    }

    private function addDefaultContentTypes()
    {

        $defaultFolderContentType = new FolderContentType($this->container, $this->requestStack);
        $this->registerContentType($defaultFolderContentType);

        $defaultMediaContentType = new MediaContentType($this->container, $this->requestStack);
        $this->registerContentType($defaultMediaContentType);

        $defaultPageContentType = new PageContentType($this->container, $this->requestStack);
        $this->registerContentType($defaultPageContentType);

    }

    /**
     * @param ContentTypeInterface $classInstance
     */
    public function registerContentType(ContentTypeInterface $classInstance)
    {
        $slug = $classInstance->getType() . '.' . $classInstance->getSchema();
        $this->contentType[$slug] = $classInstance;
    }

    /**
     * @return array
     */
    public function getRegisteredContent()
    {
        $retVal = array();
        /**
         * @var ContentTypeInterface $class
         */
        foreach ($this->contentType as $key => $class) {
            $retVal[$key] = array(
                'name' => $class->getName(),
                'type' => $class->getType(),
                'schema' => $class->getSchema()
            );
        }
        return $retVal;
    }

    /**
     * @param string $type
     * @param string $schema
     * @return ContentTypeInterface
     */
    public function getContentClass($type, $schema = 'Default')
    {
        $slug = $type . '.' . $schema;
        if (!isset($this->contentType[$slug])) {
            throw new \InvalidArgumentException("ContentType: `{$slug}` does not exists.");
        }
        return $this->contentType[$slug];
    }

    /**
     * @param ContentEntity $content
     * @param Form $form
     * @param array $fields
     * @return Form|void
     */
    public function loadFormData(ContentEntity $content = null, Form $form = null, $fields = array())
    {
        if (null === $content) {
            return;
        }
        if (null === $form) {
            return;
        }
        if (empty($fields)) {
            return;
        }

        $form->get('id')->setData($content->getId());
        $form->get('type')->setData($content->getType());
        $form->get('schema')->setData($content->getSchema());
        $form->get('title')->setData($content->getTitle());
        $form->get('summary')->setData($content->getSummary());
        $form->get('content')->setData($content->getContent());

        return $form;
    }


    public function prepareEntity(ContentEntity $content = null, $data = array(), $fields = array())
    {
        if (null === $content) {
            return;
        }
        if (empty($data)) {
            return;
        }
        if (empty($fields)) {
            return;
        }

        /**
         * @var \Bellwether\BWCMSBundle\Entity\ContentRepository $contentRepository
         */
        $contentRepository = $this->em()->getRepository('BWCMSBundle:ContentEntity');


        foreach ($fields as $fieldName => $fieldInfo) {
            if (!isset($data[$fieldName]) || empty($data[$fieldName])) {
                continue;
            }
            if ($fieldName == 'parent') {
                $parentContent = $contentRepository->find($data['parent']);
                $content->setTreeParent($parentContent);
            }
            if ($fieldName == 'type') {
                $content->setType($data['type']);
            }
            if ($fieldName == 'schema') {
                $content->setSchema($data['schema']);
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
            if ($fieldName == 'attachment') {
                $mediaInfo = $this->mm()->handleUpload($data['attachment']);
                $content->setMime($mediaInfo['mimeType']);
                $content->setFile($mediaInfo['filename']);
                $content->setSize($mediaInfo['size']);
                $content->setExtension($mediaInfo['extension']);
            }

        }
        return $content;
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


    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
    }


    public function getSystemThumbURL(ContentEntity $content = null, $width, $height)
    {
        if ($content->getFile() == null) {
            $thumbURL = $this->getThumbService()
                ->open($this->getContentTypeResourceImage($content->getType()))
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


}