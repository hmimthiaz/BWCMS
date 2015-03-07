<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\Form\ContentEmptyForm;

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

    public function getType()
    {
        return "Page";
    }

    public function getSchema()
    {
        return "Default";
    }

    abstract protected function buildForm();

    /**
     * @return Form
     */
    final public function getForm()
    {
        if ($this->form == null) {
            $this->buildForm();
            $this->setDefaultFormFields();
            $this->form = $this->fb()->getForm();
        }
        return $this->form;
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
        $this->fb()->add('type', 'hidden', array(
            'data' => $this->getType(),
        ));

        $this->fb()->add('schema', 'hidden', array(
            'data' => $this->getSchema(),
        ));
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
