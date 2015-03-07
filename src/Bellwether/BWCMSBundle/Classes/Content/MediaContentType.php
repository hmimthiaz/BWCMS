<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class MediaContentType Extends BaseContentType
{

    public function getType()
    {
        return "Media";
    }

    public function getSchema()
    {
        return "Default";
    }

}
