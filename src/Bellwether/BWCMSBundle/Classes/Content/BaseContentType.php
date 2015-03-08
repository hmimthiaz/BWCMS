<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\Form\ContentEmptyForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


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

    private $fields = array();

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
            $this->buildForm();
            $this->setDefaultFormFields();
            $this->fb()->setAction($this->generateUrl('post_save'));
            $this->fb()->setMethod('POST');
            $this->form = $this->fb()->getForm();
        }
        return $this->form;
    }

    final public function getFields()
    {
        if (!isset($this->fields['id'])) {
            $this->addField('id', ContentFieldType::String);
        }
        if (!isset($this->fields['type'])) {
            $this->addField('type', ContentFieldType::String);
        }
        if (!isset($this->fields['schema'])) {
            $this->addField('schema', ContentFieldType::String);
        }
        $this->buildFields();
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
        }
        return $this->formBuilder;
    }

    private function setDefaultFormFields()
    {
        $this->fb()->add('id', 'hidden');

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
