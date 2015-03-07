<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class PageContentType Extends BaseContentType
{
    public function getType()
    {
        return "Page";
    }

    public function getSchema()
    {
        return "Default";
    }

}
