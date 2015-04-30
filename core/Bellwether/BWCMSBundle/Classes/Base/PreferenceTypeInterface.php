<?php

namespace Bellwether\BWCMSBundle\Classes\Base;



interface PreferenceTypeInterface
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

}
