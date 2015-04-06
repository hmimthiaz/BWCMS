<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceType;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceTypeInterface;
use Bellwether\BWCMSBundle\Classes\Preference\Type\GeneralType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class PreferenceManager extends BaseService
{

    private $preferenceType = array();


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
        $slug = strtolower($classInstance->getType());
        $this->preferenceType[$slug] = $classInstance;
    }

    public function getRegisteredOptionTypes()
    {
        $retVal = array();
        /**
         * @var PreferenceType $class
         */
        foreach ($this->preferenceType as $key => $class) {
            $retVal[$key] = array(
                'name' => $class->getName(),
                'type' => strtolower($class->getType()),
                'class' => $class
            );
        }
        return $retVal;
    }

    /**
     * @param string $type
     * @return PreferenceType
     */
    public function getPreferenceClass($type)
    {
        $slug = strtolower($type);
        if (!isset($this->preferenceType[$slug])) {
            throw new \InvalidArgumentException("PreferenceType: `{$slug}` does not exists.");
        }
        return $this->preferenceType[$slug];
    }


    /**
     * @param Form $form
     * @param PreferenceTypeInterface|PreferenceType $classInstance
     * @return Form|void
     */
    final public function loadFormData( Form $form = null, PreferenceTypeInterface $classInstance)
    {
        if (null === $form) {
            return;
        }

        $fields = $classInstance->getFields();

        $this->dump($fields);
        exit;


//        $form = $classInstance->loadFormData($content, $form);
        return $form;
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\ContentRepository
     */
    public function getPreferenceRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:PreferenceEntity');
    }

}