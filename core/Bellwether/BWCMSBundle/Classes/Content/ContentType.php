<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Bellwether\BWCMSBundle\Classes\Base\ContentTypeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentScopeType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilder;

use Bellwether\BWCMSBundle\Classes\Service\AdminService;
use Bellwether\BWCMSBundle\Classes\Service\SiteService;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\ContentQueryService;
use Bellwether\BWCMSBundle\Classes\Service\LocaleService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;
use Bellwether\BWCMSBundle\Classes\Service\MailService;
use Bellwether\BWCMSBundle\Classes\Service\PreferenceService;
use Bellwether\BWCMSBundle\Classes\Service\TemplateService;

use Bellwether\BWCMSBundle\Classes\Content\Form\ContentEmptyForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Symfony\Component\Routing\RouteCollection;

use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Bundle\TwigBundle\TwigEngine;

abstract class ContentType implements ContentTypeInterface
{


    /**
     * @var FormBuilder
     */
    private $formBuilder = null;

    /**
     * @var Form
     */
    private $form = null;

    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * @var RequestStack
     *
     * @api
     */
    protected $requestStack;

    private $parentId = '';

    private $fields = null;

    private $templates = null;

    private $path = null;

    /**
     * @var bool
     */
    private $isHierarchy = false;

    /**
     * @var bool
     */
    private $isRootItem = false;

    /**
     * @var bool
     */
    private $isTaxonomy = false;

    /**
     * @var bool
     */
    private $isSummaryEnabled = true;

    /**
     * @var bool
     */
    private $isContentEnabled = true;

    /**
     * @var bool
     */
    private $isSlugEnabled = false;

    /**
     * @var bool
     */
    private $isSortEnabled = false;

    /**
     * @var bool
     */
    private $isUploadEnabled = false;

    /**
     * @var bool
     */
    private $isIndexed = false;

    /**
     * @var bool
     */
    private $isPublishDateEnabled = false;

    /**
     * @var bool
     */
    private $isExpireDateEnabled = false;

    /**
     * @var bool
     */
    private $isPageBuilderSupported = false;

    /**
     * @var bool
     */
    private $isSeoFieldsEnabled = false;

    private $taxonomyRelations = null;

    public function getType()
    {
        return "Content";
    }

    public function getSchema()
    {
        return "Page";
    }

    public function getName()
    {
        return "Page";
    }

    public function setParent($contentId = null)
    {
        $this->parentId = $contentId;
    }

    final public function addField($fieldName, $type, $isIndexed = false)
    {
        $this->fields[$fieldName] = array(
            'name' => $fieldName,
            'type' => $type,
            'isIndexed' => $isIndexed
        );
    }

    final public function getFieldType($fieldName)
    {
        if (!isset($this->fields[$fieldName])) {
            return null;
        }
        return $this->fields[$fieldName]['type'];
    }

    abstract protected function buildFields();

    abstract protected function buildForm($isEditMode = false);

    /**
     * @return string
     */
    abstract public function getImage();

    public function addTemplate($templateName, $templateFile, $templateImage)
    {
        $templateImagePath = str_replace('.', DIRECTORY_SEPARATOR, $this->getType() . '.' . $this->getSchema());
        $templateImagePath = $this->tp()->getCurrentSkin()->getPath() . DIRECTORY_SEPARATOR . $templateImagePath . DIRECTORY_SEPARATOR . $templateImage;
        $templateImagePath = $this->getThumbService()->open($templateImagePath)->resize(240, 200)->cacheFile('guess');
        $this->templates[] = array(
            'title' => $templateName,
            'template' => $templateFile,
            'image' => $templateImagePath
        );
    }

    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }
        return $this->path;
    }

    abstract public function addTemplates();

    /**
     * @return array
     */
    public function getTemplates()
    {
        if (is_null($this->templates)) {
            $this->templates = array();
            $this->addTemplates();
        }
        return $this->templates;
    }

    /**
     * @return null|RouteCollection
     */
    abstract public function getRouteCollection();

    /**
     * @param ContentEntity $contentEntity
     * @return string|null
     */
    abstract public function getPublicURL($contentEntity, $full = false);

    /**
     * @return Form
     */
    final public function getForm($isEditMode = false)
    {
        if ($this->form == null) {
            $this->setDefaultFormFields($isEditMode);
            $this->buildForm();
            $this->setDefaultHiddenFormFields($isEditMode);
            $this->fb()->setAction($this->generateUrl('_bwcms_admin_content_save'));
            $this->fb()->setMethod('POST');
            $this->form = $this->fb()->getForm();
        }
        return $this->form;
    }

    final public function getFields()
    {
        if ($this->fields == null) {
            $this->fields = array();
            $this->addField('id', ContentFieldType::Internal);
            $this->addField('type', ContentFieldType::Internal);
            $this->addField('schema', ContentFieldType::Internal);
            $this->addField('scope', ContentFieldType::Internal);
            $this->addField('template', ContentFieldType::Internal);
            $this->addField('parent', ContentFieldType::Internal);
            $this->addField('title', ContentFieldType::Internal, $this->isIndexed());
            if ($this->isSummaryEnabled) {
                $this->addField('summary', ContentFieldType::Internal, $this->isIndexed());
            }
            if ($this->isContentEnabled) {
                $this->addField('content', ContentFieldType::Internal, $this->isIndexed());
            }
            if ($this->isUploadEnabled) {
                $this->addField('attachment', ContentFieldType::Internal);
            }
            if ($this->isPublishDateEnabled()) {
                $this->addField('publishDate', ContentFieldType::Internal);
            }
            if ($this->isExpireDateEnabled()) {
                $this->addField('expireDate', ContentFieldType::Internal);
            }
            $this->addField('status', ContentFieldType::Internal);
            $this->addField('slug', ContentFieldType::Internal);
            $this->addField('sortBy', ContentFieldType::Internal);
            $this->addField('sortOrder', ContentFieldType::Internal);
            if ($this->isSeoFieldsEnabled()) {
                $this->addField('pageTitle', ContentFieldType::String, $this->isIndexed());
                $this->addField('pageDescription', ContentFieldType::String, $this->isIndexed());
                $this->addField('pageKeywords', ContentFieldType::String, $this->isIndexed());
            }
            $this->buildFields();
        }
        return $this->fields;
    }

    final public function getIndexedFields()
    {
        $returnVar = array();
        $fields = $this->getFields();
        if (!empty($fields)) {
            foreach ($fields as $fieldInfo) {
                if ($fieldInfo['isIndexed']) {
                    $returnVar[] = $fieldInfo['name'];
                }
            }
        }
        return $returnVar;
    }

    final public function addTaxonomyRelation($name, $schema, $isMultiple = true, $required = true)
    {
        if (is_null($this->taxonomyRelations)) {
            $this->taxonomyRelations = array();
        }
        $this->taxonomyRelations[$name] = array(
            'title' => ucwords(strtolower($name)),
            'name' => $name,
            'fieldName' => strtolower('Taxonomy_' . $name),
            'multiple' => $isMultiple,
            'required' => $required,
            'type' => 'Taxonomy',
            'schema' => $schema
        );
    }

    /**
     * @return FormBuilder
     */
    final public function fb()
    {
        if ($this->formBuilder == null) {
            $contentEmptyForm = new ContentEmptyForm();
            $this->formBuilder = $this->container->get('form.factory')->createBuilder($contentEmptyForm);
            $this->formBuilder->addEventListener(FormEvents::POST_SUBMIT, array(&$this, 'formEventPostSubmit'));
        }
        return $this->formBuilder;
    }

    final public function formEventPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (empty($data['title'])) {
            $form->get('title')->addError(new FormError('Title cannot be empty!'));
        }

        if ($this->isUploadEnabled) {
            if (!($data['attachment'] instanceof UploadedFile) && empty($data['id'])) {
                $form->get('attachment')->addError(new FormError('Attachment cannot be empty'));
            }
            if ($data['attachment'] instanceof UploadedFile) {
                if (!($data['attachment']->isValid())) {
                    $form->get('attachment')->addError(new FormError($data['attachment']->getErrorMessage()));
                }
            }
        }
        if (!empty($data['slug'])) {
            if ($this->cm()->checkSlugExists($data['slug'], $this->getType(), $data['parent'], $data['id'])) {
                $form->get('slug')->addError(new FormError('Slug already exists!'));
            }
        }
        $this->validateForm($event);
    }

    /**
     * @return ContentEntity
     */
    public function getNewContent()
    {
        $contentEntity = new ContentEntity();
        if ($this->isHierarchy()) {
            $contentEntity->setSortBy(ContentSortByType::SortIndex);
            $contentEntity->setSortOrder(ContentSortOrderType::ASC);
        } else {
            $contentEntity->setSortBy(ContentSortByType::Published);
            $contentEntity->setSortOrder(ContentSortOrderType::DESC);
        }
        if ($this->isUploadEnabled() || $this->isHierarchy()) {
            $contentEntity->setStatus(ContentPublishType::Published);
        } else {
            $contentEntity->setStatus(ContentPublishType::Draft);
        }
        if ($this->isPublishDateEnabled()) {
            $contentEntity->setPublishDate(new \DateTime());
        }
        if ($this->isExpireDateEnabled()) {
            $contentEntity->setExpireDate(new \DateTime());
        }
        $contentEntity->setScope(ContentScopeType::CPublic);
        return $contentEntity;
    }

    abstract function validateForm(FormEvent $event);

    abstract public function loadFormData(ContentEntity $content = null, Form $form = null);

    abstract public function prepareEntity(ContentEntity $content = null, Form $form = null);


    /**
     * @param ContentEntity $contentEntity
     * @param array $options
     * @return null
     */
    public function render($contentEntity, $options = array())
    {
        return null;
    }

    public function loadCustomField($fieldName, $fieldValue)
    {
        return $fieldValue;
    }

    public function getSearchTextForField($fieldName, $fieldValue)
    {
        return '';
    }


    private function setDefaultFormFields($isEditMode = false)
    {
        $currentSite = $this->sm()->getAdminCurrentSite();

        $this->fb()->add('title', 'text',
            array(
                'required' => true,
                'label' => 'Title',
                'attr' => array(
                    'dir' => $currentSite->getDirection()
                )
            )
        );

        if ($isEditMode && $this->isSlugEnabled()) {
            $this->fb()->add('slug', 'text',
                array(
                    'required' => true,
                    'label' => 'URL Slug',
                    'attr' => array(
                        'dir' => $currentSite->getDirection()
                    )
                )
            );
        } else {
            $this->fb()->add('slug', 'hidden');
        }

        if ($this->isSummaryEnabled()) {
            $this->fb()->add('summary', 'textarea',
                array(
                    'required' => false,
                    'label' => 'Summary',
                    'attr' => array(
                        'dir' => $currentSite->getDirection()
                    )
                )
            );
        }

        if ($this->isContentEnabled()) {
            $this->fb()->add('content', 'textarea',
                array(
                    'required' => false,
                    'label' => 'Content',
                    'attr' => array(
                        'dir' => $currentSite->getDirection(),
                        'class' => 'editor'
                    )
                )
            );
        }

    }

    private function setDefaultHiddenFormFields($isEditMode = false)
    {

        $relations = $this->getTaxonomyRelations();
        if (!empty($relations)) {
            foreach ($relations as $relation) {
                $taxonomyClass = $this->cm()->getContentClass($relation['type'], $relation['schema']);
                $terms = $this->cm()->getTaxonomyTerms($taxonomyClass);
                $constraints = array();
                if ($relation['required']) {
                    $constraints[] = new NotBlank(array('message' => 'Please select an item.'));
                }
                if ($taxonomyClass->isHierarchy()) {
                    $this->fb()->add($relation['fieldName'], 'bwcms_taxonomy_tree',
                        array(
                            'label' => $relation['title'],
                            'nodes' => $terms,
                            'constraints' => $constraints
                        )
                    );

                } else {
                    $this->fb()->add($relation['fieldName'], 'choice',
                        array(
                            'choices' => $terms,
                            'label' => $relation['title'],
                            'expanded' => $relation['multiple'],
                            'multiple' => $relation['multiple'],
                            'constraints' => $constraints
                        )
                    );
                }
            }
        }

        if ($this->isUploadEnabled()) {
            $this->fb()->add('attachment', 'file',
                array(
                    'label' => 'Attachment'
                )
            );
        }

        if ($this->isSeoFieldsEnabled()) {
            $this->fb()->add('pageTitle', 'text',
                array(
                    'required' => false,
                    'label' => 'Page Title',
                    'attr' => array(
                        'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                    )
                )
            );

            $this->fb()->add('pageDescription', 'text',
                array(
                    'required' => false,
                    'label' => 'Page Description',
                    'attr' => array(
                        'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                    )
                )
            );

            $this->fb()->add('pageKeywords', 'text',
                array(
                    'required' => false,
                    'label' => 'Page Keywords',
                    'attr' => array(
                        'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                    )
                )
            );
        }

        if ($isEditMode && $this->isHierarchy() && $this->isSortEnabled()) {
            $this->fb()->add('sortBy', 'choice', array(
                'choices' => array(
                    ContentSortByType::SortIndex => 'Sort',
                    ContentSortByType::Created => 'Created',
                    ContentSortByType::Published => 'Published',
                    ContentSortByType::Title => 'Title',
                    ContentSortByType::Size => 'Size',
                ),
                'label' => 'Sort By'
            ));
            $this->fb()->add('sortOrder', 'choice', array(
                'choices' => array(
                    ContentSortOrderType::DESC => 'DESC [Z-A]',
                    ContentSortOrderType::ASC => 'ASC [A-Z]',
                ),
                'label' => 'Sort Order'
            ));
        } else {
            $this->fb()->add('sortBy', 'hidden');
            $this->fb()->add('sortOrder', 'hidden');
        }

        $templates = $this->getTemplates();
        if (count($templates) == 1) {
            $this->fb()->add('template', 'hidden', array(
                'data' => $templates[0]['template'],
            ));
        } else {
            $this->fb()->add('template', 'bwcms_content_template',
                array(
                    'label' => 'Template',
                    'choices' => $templates
                )
            );
        }

        if ($this->isPublishDateEnabled()) {
            $this->fb()->add('publishDate', 'datetime',
                array(
                    'label' => 'Publish Time',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd HH:mm',
                    'attr' => array(
                        'class' => 'contentDate'
                    )
                )
            );
        }

        if ($this->isExpireDateEnabled()) {
            $this->fb()->add('expireDate', 'datetime',
                array(
                    'label' => 'Expire Time',
                    'widget' => 'single_text',
                    'format' => 'yyyy-MM-dd HH:mm',
                    'attr' => array(
                        'class' => 'contentDate'
                    )
                )
            );
        }

        $this->fb()->add('status', 'choice',
            array(
                'label' => 'Status',
                'choices' => array(
                    ContentPublishType::Draft => 'Draft',
                    ContentPublishType::Published => 'Published',
                    ContentPublishType::Expired => 'Expired'
                )
            )
        );

        $this->fb()->add('id', 'hidden');
        $this->fb()->add('scope', 'hidden');

        $this->fb()->add('parent', 'hidden', array(
            'data' => $this->parentId,
        ));

        $this->fb()->add('type', 'hidden', array(
            'data' => $this->getType(),
        ));

        $this->fb()->add('schema', 'hidden', array(
            'data' => $this->getSchema(),
        ));
        $this->fb()->add('save', 'submit', array(
            'attr' => array('class' => 'save'),
        ));
    }

    /**
     * @return TwigEngine
     */
    public function getTwigEngine()
    {
        return $this->container->get('templating');
    }

    /**
     * @param ContentEntity $contentEntity
     * @return string
     */
    public function getContentTemplate($contentEntity)
    {
        return $this->tp()->getCurrentSkin()->getTemplateName($this->cq()->getContentTemplate($contentEntity));
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route The name of the route
     * @param mixed $parameters An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return AdminService
     */
    public function admin()
    {
        return $this->container->get('BWCMS.Admin')->getManager();
    }

    /**
     * @return SiteService
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site')->getManager();
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return ContentQueryService
     */
    public function cq()
    {
        return $this->container->get('BWCMS.ContentQuery')->getManager();
    }

    /**
     * @return LocaleService
     */
    public function locale()
    {
        return $this->container->get('BWCMS.Locale')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }

    /**
     * @return PreferenceService
     */
    public function pref()
    {
        return $this->container->get('BWCMS.Preference')->getManager();
    }

    /**
     * @return TemplateService
     */
    public function tp()
    {
        return $this->container->get('BWCMS.Template')->getManager();
    }

    /**
     * @return MailService
     */
    public function mailer()
    {
        return $this->container->get('BWCMS.Mailer');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return boolean
     */
    public function isHierarchy()
    {
        return $this->isHierarchy;
    }

    /**
     * @param boolean $isHierarchy
     */
    public function setIsHierarchy($isHierarchy)
    {
        $this->isHierarchy = $isHierarchy;
    }

    /**
     * @return boolean
     */
    public function isRootItem()
    {
        return $this->isRootItem;
    }

    /**
     * @param boolean $isRootItem
     */
    public function setIsRootItem($isRootItem)
    {
        $this->isRootItem = $isRootItem;
    }

    /**
     * @return boolean
     */
    public function isTaxonomy()
    {
        return $this->isTaxonomy;
    }

    /**
     * @param boolean $isTaxonomy
     */
    public function setIsTaxonomy($isTaxonomy)
    {
        $this->isTaxonomy = $isTaxonomy;
    }

    public function isType($type = 'Content', $schema = null)
    {
        if (is_null($type)) {
            return true;
        }
        if (strtoupper($this->getType()) == strtoupper($type)) {
            if (is_null($schema) || empty($schema)) {
                return true;
            }
            if (strtoupper($this->getSchema()) == strtoupper($schema)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function isSummaryEnabled()
    {
        return $this->isSummaryEnabled;
    }

    /**
     * @param boolean $isSummaryEnabled
     */
    public function setIsSummaryEnabled($isSummaryEnabled)
    {
        $this->isSummaryEnabled = $isSummaryEnabled;
    }

    /**
     * @return boolean
     */
    public function isContentEnabled()
    {
        return $this->isContentEnabled;
    }

    /**
     * @param boolean $isContentEnabled
     */
    public function setIsContentEnabled($isContentEnabled)
    {
        $this->isContentEnabled = $isContentEnabled;
    }

    /**
     * @return boolean
     */
    public function isUploadEnabled()
    {
        return $this->isUploadEnabled;
    }

    /**
     * @param boolean $isUploadEnabled
     */
    public function setIsUploadEnabled($isUploadEnabled)
    {
        $this->isUploadEnabled = $isUploadEnabled;
    }

    /**
     * @return boolean
     */
    public function isPublishDateEnabled()
    {
        return $this->isPublishDateEnabled;
    }

    /**
     * @param boolean $isPublishDateEnabled
     */
    public function setIsPublishDateEnabled($isPublishDateEnabled)
    {
        $this->isPublishDateEnabled = $isPublishDateEnabled;
    }

    /**
     * @return boolean
     */
    public function isExpireDateEnabled()
    {
        return $this->isExpireDateEnabled;
    }

    /**
     * @param boolean $isExpireDateEnabled
     */
    public function setIsExpireDateEnabled($isExpireDateEnabled)
    {
        $this->isExpireDateEnabled = $isExpireDateEnabled;
    }

    /**
     * @return boolean
     */
    public function isSortEnabled()
    {
        return $this->isSortEnabled;
    }

    /**
     * @param boolean $isSortEnabled
     */
    public function setIsSortEnabled($isSortEnabled)
    {
        $this->isSortEnabled = $isSortEnabled;
    }

    /**
     * @return boolean
     */
    public function isIndexed()
    {
        return $this->isIndexed;
    }

    /**
     * @param boolean $isIndexed
     */
    public function setIsIndexed($isIndexed)
    {
        $this->isIndexed = $isIndexed;
    }

    /**
     * @return boolean
     */
    public function isSlugEnabled()
    {
        return $this->isSlugEnabled;
    }

    /**
     * @param boolean $isSlugEnabled
     */
    public function setIsSlugEnabled($isSlugEnabled)
    {
        $this->isSlugEnabled = $isSlugEnabled;
    }

    /**
     * @return boolean
     */
    public function isPageBuilderSupported()
    {
        return $this->isPageBuilderSupported;
    }

    /**
     * @param boolean $isPageBuilderSupported
     */
    public function setIsPageBuilderSupported($isPageBuilderSupported)
    {
        $this->isPageBuilderSupported = $isPageBuilderSupported;
    }

    /**
     * @return boolean
     */
    public function isSeoFieldsEnabled()
    {
        return $this->isSeoFieldsEnabled;
    }

    /**
     * @param boolean $isSeoFieldsEnabled
     */
    public function setIsSeoFieldsEnabled($isSeoFieldsEnabled)
    {
        $this->isSeoFieldsEnabled = $isSeoFieldsEnabled;
    }

    /**
     * @return null
     */
    public function getTaxonomyRelations()
    {
        return $this->taxonomyRelations;
    }


    public function dump($var, $maxDepth = 2, $stripTags = true)
    {
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }

}
