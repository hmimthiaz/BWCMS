<?php

namespace Bellwether\BWCMSBundle\Classes\Option\Type;

use Bellwether\BWCMSBundle\Classes\Option\OptionTypeInterface;
use Bellwether\BWCMSBundle\Classes\Option\OptionType;


class GeneralType Extends OptionType
{

    public function getType()
    {
        return 'General';
    }

    public function getForm()
    {

    }

    public function getFields()
    {

    }

    public function getName()
    {
        return "General";
    }

}