<?php

namespace Bellwether\BWCMSBundle\Classes\Content;

class FolderContentType Extends BaseContentType
{

    public function getType()
    {
        return "Folder";
    }

    public function getSchema()
    {
        return "Default";
    }

}
