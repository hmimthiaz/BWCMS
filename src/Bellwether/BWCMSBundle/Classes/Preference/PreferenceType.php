<?php
namespace Bellwether\BWCMSBundle\Classes\Preference;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Preference\Form\PreferenceEmptyForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Bellwether\BWCMSBundle\Classes\SiteManager;
use Bellwether\BWCMSBundle\Classes\ContentManager;
use Bellwether\BWCMSBundle\Classes\MediaManager;
use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;

abstract class PreferenceType implements PreferenceTypeInterface
{
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


    /**
     * @var FormBuilder
     */
    private $formBuilder = null;

    /**
     * @var Form
     */
    private $form = null;

    private $fields = null;

    /**
     * @return FormBuilder
     */
    final public function fb()
    {
        if ($this->formBuilder == null) {
            $contentEmptyForm = new PreferenceEmptyForm();
            $this->formBuilder = $this->container->get('form.factory')->createBuilder($contentEmptyForm);
            $this->formBuilder->addEventListener(FormEvents::POST_SUBMIT, array(&$this, 'formEventPostSubmit'));
        }
        return $this->formBuilder;
    }

    final public function addField($fieldName, $type, $global = false)
    {
        $this->fields[$fieldName] = array(
            'name' => $fieldName,
            'type' => $type,
            'global' => $global
        );
    }

    final public function getFields()
    {
        if ($this->fields == null) {
            $this->fields = array();
            $this->buildFields();
        }
        return $this->fields;
    }

    abstract protected function buildFields();

    /**
     * @return Form
     */
    final public function getForm()
    {
        if ($this->form == null) {
            $this->buildForm();
            $this->setDefaultHiddenFormFields();
            $this->fb()->setAction($this->generateUrl('preference_save_page', array('type' => $this->getType())));
            $this->fb()->setMethod('POST');
            $this->form = $this->fb()->getForm();
        }
        return $this->form;
    }

    abstract protected function buildForm();


    final public function formEventPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $this->validateForm($event);
    }

    abstract function validateForm(FormEvent $event);

//    abstract public function loadFormData(OptionEntity $option = null, Form $form = null);
//
//    abstract public function prepareEntity(OptionEntity $content = null, $data = array());

    private function setDefaultHiddenFormFields()
    {
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
}