<?php

namespace Bellwether\BWCMSBundle\Classes\Base;

use \Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RequestStack;


class BWCMSBaseService  extends ContainerAware
{

    /**
     * @var RequestStack
     *
     * @api
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack = null)
    {
        $this->requestStack = $requestStack;
    }


    public function dump($var, $maxDepth = 2, $stripTags = true){
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }

}
