<?php

namespace Bellwether\BWCMSBundle\Classes\Preference;



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
