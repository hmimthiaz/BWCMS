<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

interface ContentTypeInterface
{
    public function getType();

    public function getSchema();

    public function getForm();
}
