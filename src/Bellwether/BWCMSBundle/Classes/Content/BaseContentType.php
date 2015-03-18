<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\Form\ContentEmptyForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Bellwether\BWCMSBundle\Classes\SiteManager;
use Bellwether\BWCMSBundle\Classes\ContentManager;
use Bellwether\BWCMSBundle\Classes\MediaManager;

use Bellwether\BWCMSBundle\Entity\ContentEntity;


abstract class BaseContentType implements ContentTypeInterface
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
    private $isUploadEnabled = false;

    public function getType()
    {
        return "Page";
    }

    public function getSchema()
    {
        return "Default";
    }

    public function getName()
    {
        return "Page";
    }

    public function setParent($contentId = null)
    {
        $this->parentId = $contentId;
    }

    final public function addField($fieldName, $type)
    {
        $this->fields[$fieldName] = array(
            'name' => $fieldName,
            'type' => $type
        );
    }

    abstract protected function buildFields();

    abstract protected function buildForm();

    /**
     * @return Form
     */
    final public function getForm()
    {
        if ($this->form == null) {
            $this->setDefaultFormFields();
            $this->buildForm();
            $this->setDefaultHiddenFormFields();
            $this->fb()->setAction($this->generateUrl('content_save'));
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
            $this->addField('parent', ContentFieldType::Internal);

            $this->addField('title', ContentFieldType::Internal);
            if ($this->isSummaryEnabled) {
                $this->addField('summary', ContentFieldType::Internal);
            }
            if ($this->isContentEnabled) {
                $this->addField('content', ContentFieldType::Internal);
            }
            if ($this->isSlugEnabled) {
                $this->addField('slug', ContentFieldType::Internal);
            }
            if ($this->isUploadEnabled) {
                $this->addField('attachment', ContentFieldType::Internal);
            }

            $this->addField('status', ContentFieldType::Internal);
            $this->buildFields();
        }
        return $this->fields;
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
        if ($this->isSlugEnabled) {
            if (empty($data['slug'])) {
                $form->get('slug')->addError(new FormError('Slug cannot be empty!'));
            } else {
                if ($this->cm()->checkSlugExists($data['slug'], $this->getType(), $data['parent'], $data['id'])) {
                    $form->get('slug')->addError(new FormError('Slug already exists!'));
                }
            }

        }
        $this->validateForm($event);
    }

    abstract function validateForm(FormEvent $event);

    abstract public function loadFormData(ContentEntity $content = null, Form $form = null);

    abstract public function prepareEntity(ContentEntity $content = null, $data = array());


    private function setDefaultFormFields()
    {
        $this->fb()->add('title', 'text',
            array(
                'max_length' => 100,
                'required' => true,
                'label' => 'Title'
            )
        );

        if ($this->isSummaryEnabled) {
            $this->fb()->add('summary', 'textarea',
                array(
                    'max_length' => 100,
                    'required' => false,
                    'label' => 'Summary'
                )
            );
        }

        if ($this->isContentEnabled) {
            $this->fb()->add('content', 'textarea',
                array(
                    'max_length' => 100,
                    'required' => false,
                    'label' => 'Content',
                    'attr' => array(
                        'class' => 'editor'
                    )
                )
            );
        }

        if ($this->isSlugEnabled) {
            $this->fb()->add('slug', 'text',
                array(
                    'max_length' => 100,
                    'required' => true,
                    'label' => 'Page Slug'
                )
            );
        }

        if ($this->isUploadEnabled) {
            $this->fb()->add('attachment', 'file',
                array(
                    'label' => 'Attachment'
                )
            );
        }

    }

    private function setDefaultHiddenFormFields()
    {

        $this->fb()->add('status', 'choice',
            array(
                'label' => 'Status',
                'choices' => array(
                    'Draft' => 'Draft',
                    'Publish' => 'Publish'
                )
            )
        );

        $this->fb()->add('id', 'hidden');

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
     * @return SiteManager
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site')->getManager();
    }

    /**
     * @return ContentManager
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return MediaManager
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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
    public function isIsSummaryEnabled()
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
    public function isIsContentEnabled()
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
    public function isIsUploadEnabled()
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
    public function isIsSlugEnabled()
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

}
