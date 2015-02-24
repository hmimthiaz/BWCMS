<?php

namespace Bellwether\BWCMSBundle\Classes\Base;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BWCMSBaseBundle extends Bundle
{

    public function boot(){
         //print "<h1>I was called here</h1>";
    }

    public function shutdown(){

    }

    public function dump($var, $maxDepth = 2, $stripTags = true){
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }


}
