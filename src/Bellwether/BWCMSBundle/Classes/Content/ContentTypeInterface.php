<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

use Symfony\Component\Form\Form;


interface ContentTypeInterface
{

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getSchema();

    /**
     * @return Form
     */
    public function getForm();

    /**
     * @return Array
     */
    public function getFields();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $contentId
     * @return mixed
     */
    public function setParent($contentId);
}
