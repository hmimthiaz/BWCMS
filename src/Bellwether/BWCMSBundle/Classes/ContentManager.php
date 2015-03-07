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
        $this->contentType[$classInstance->getType()] = $classInstance;
    }

    /**
     * @param $contentType
     * @return ContentTypeInterface
     */
    public function getContentClass($contentType)
    {
        if (!isset($this->contentType[$contentType])) {
            throw new \InvalidArgumentException("ContentType: `{$contentType}` does not exists.");
        }
        return $this->contentType[$contentType];
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


    public function getSystemThumbURL($type, $width, $height)
    {
        $thumbURL = $this->getThumbService()->open($this->getContentTypeResourceImage($type))->resize($width, $height)->cacheFile('guess');
        return $thumbURL;
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