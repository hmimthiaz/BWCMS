<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;


class OptionManager extends BaseService
{

    private $optionType = array();


    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SiteManager
     */
    public function getManager()
    {
        return $this;
    }


    private function addDefaultOptionTypes()
    {

    }

    /**
     * @param ContentTypeInterface|ContentType $classInstance
     */
    public function registerContentType(ContentTypeInterface $classInstance)
    {
        $slug = $classInstance->getType() . '.' . $classInstance->getSchema();
        $this->contentType[$slug] = $classInstance;
    }

}