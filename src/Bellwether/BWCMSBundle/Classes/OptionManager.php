<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Option\PreferenceType;
use Bellwether\BWCMSBundle\Classes\Option\PreferenceTypeInterface;

use Bellwether\BWCMSBundle\Classes\Option\Type\GeneralType;


class OptionManager extends BaseService
{

    private $optionType = array();


    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
        $this->addDefaultOptionTypes();
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
     * @param PreferenceTypeInterface|PreferenceType $classInstance
     */
    public function registerOptionType(PreferenceTypeInterface $classInstance)
    {
        $slug = $classInstance->getType();
        $this->optionType[$slug] = $classInstance;
    }

    public function getRegisteredOptionTypes()
    {
        $retVal = array();
        /**
         * @var PreferenceType $class
         */
        foreach ($this->optionType as $key => $class) {
            $retVal[$key] = array(
                'name' => $class->getName(),
                'type' => $class->getType(),
                'class' => $class
            );
        }
        return $retVal;
    }

}