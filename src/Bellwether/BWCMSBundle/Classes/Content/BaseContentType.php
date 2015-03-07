<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class BaseContentType implements ContentTypeInterface
{

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

    public function getForm()
    {
        if ($this->form == null) {
            $this->form = new ContentEntityForm();
        }

        // $this->container->get('form.factory')->create($type, $data, $options);
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
