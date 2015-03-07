<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

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

    }

    public function getSchema()
    {

    }

    /**
     * @return Form
     */
    public function getForm()
    {
        if ($this->form == null) {


        }
        return $this->form;
    }

    /**
     * @return FormBuilder
     */
    public function fb()
    {
        if ($this->formBuilder == null) {
            $this->formBuilder = $this->container->get('form.factory')->createBuilder(array());
        }
        return $this->formBuilder;
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
