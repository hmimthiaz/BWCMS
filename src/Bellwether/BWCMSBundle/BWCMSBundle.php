<?php

namespace Bellwether\BWCMSBundle;

use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseBundle;

class BWCMSBundle extends BWCMSBaseBundle
{

    public function dump($var, $maxDepth = 2, $stripTags = true){
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
    }

}
