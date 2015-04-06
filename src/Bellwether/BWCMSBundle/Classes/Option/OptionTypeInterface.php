<?php

namespace Bellwether\BWCMSBundle\Classes\Option;



interface OptionTypeInterface
{

    /**
     * @return string
     */
    public function getType();

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
     * @param string $type
     * @return bool
     */
    public function isType($type = '');
}
