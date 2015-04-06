<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Option\OptionType;
use Bellwether\BWCMSBundle\Classes\Option\OptionTypeInterface;

use Bellwether\BWCMSBundle\Classes\Option\Type\GeneralType;


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
        $this->registerOptionType(new GeneralType($this->container, $this->requestStack));

    }

    /**
     * @param OptionTypeInterface|OptionType $classInstance
     */
    public function registerOptionType(OptionTypeInterface $classInstance)
    {
        $slug = $classInstance->getType();
        $this->optionType[$slug] = $classInstance;
    }

}