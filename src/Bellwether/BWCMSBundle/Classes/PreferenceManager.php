<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceType;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceTypeInterface;
use Bellwether\BWCMSBundle\Classes\Preference\Type\GeneralType;
use Bellwether\BWCMSBundle\Classes\Preference\Type\EmailSMTPType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;

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
        $this->registerOptionType(new EmailSMTPType($this->container, $this->requestStack));
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
    final public function loadFormData(Form $form = null, PreferenceTypeInterface $classInstance)
    {
        if (null === $form) {
            return;
        }

        $preferenceRepo = $this->getPreferenceRepository();
        $fields = $classInstance->getFields();

        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldInfo) {
                $fieldType = $fieldInfo['type'];
                $criteria = array(
                    'field' => $fieldInfo['name'],
                    'fieldType' => $fieldType,
                    'type' => $classInstance->getType()
                );
                if (!$fieldInfo['global']) {
                    $criteria['site'] = $this->sm()->getCurrentSite()->getId();
                }else{
                    $criteria['site'] = null;
                }
                /**
                 * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
                 */
                $preferenceEntity = $preferenceRepo->findOneBy($criteria);
                if (is_null($preferenceEntity)) {
                    continue;
                }
                try {
                    $formField = $form->get($fieldName);
                } catch (\OutOfBoundsException $e) {
                    continue;
                }
                $fieldValue = $preferenceEntity->getValue();

                if ($fieldType == PreferenceFieldType::String || $fieldValue == PreferenceFieldType::Number) {
                    $formField->setData($fieldValue);
                }
                if ($fieldType == PreferenceFieldType::Content) {
                    $formField->setData($fieldValue);
                }
                if ($fieldType == PreferenceFieldType::Date || $fieldType == PreferenceFieldType::Time || $fieldType == PreferenceFieldType::DateTime) {
                    $dateValue = new \DateTime($fieldValue);
                    $formField->setData($dateValue);
                }
                if ($fieldType == PreferenceFieldType::Serialized) {
                    try {
                        $data = $this->getSerializer()->deserialize($fieldValue, 'ArrayCollection', 'json');
                    } catch (\RuntimeException $exp) {
                        $data = array();
                    }
                    $data = $this->loadSerializedData($data);
                    $formField->setData($data);
                }
            }
        }
//        $form = $classInstance->loadFormData($content, $form);
        return $form;
    }

    private function loadSerializedData($value)
    {
        usort($value, function ($a, $b) {
            if (isset($a['__sort__']) && isset($b['__sort__'])) {
                return $a['__sort__'] - $b['__sort__'];
            }
            return 0;
        });
        return $value;
    }

    /**
     * @param Form $form
     * @param PreferenceTypeInterface|PreferenceType $classInstance
     */
    final public function saveFormData(Form $form = null, PreferenceTypeInterface $classInstance)
    {
        if (null === $form) {
            return;
        }

        $preferenceRepo = $this->getPreferenceRepository();
        $fields = $classInstance->getFields();

        $data = $form->getData();

        if (!empty($fields)) {
            foreach ($fields as $fieldName => $fieldInfo) {
                $fieldType = $fieldInfo['type'];
                $fieldValue = $data[$fieldName];
                $criteria = array(
                    'field' => $fieldInfo['name'],
                    'fieldType' => $fieldType,
                    'type' => $classInstance->getType()
                );
                if (!$fieldInfo['global']) {
                    $criteria['site'] = $this->sm()->getCurrentSite()->getId();
                }else{
                    $criteria['site'] = null;
                }

                /**
                 * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
                 */
                $preferenceEntity = $preferenceRepo->findOneBy($criteria);
                if (is_null($preferenceEntity) && !is_null($fieldValue)) {
                    $preferenceEntity = new PreferenceEntity();
                    $preferenceEntity->setType($classInstance->getType());
                    $preferenceEntity->setField($fieldName);
                    $preferenceEntity->setFieldType($fieldType);
                    if (!$fieldInfo['global']) {
                        $preferenceEntity->setSite($this->sm()->getCurrentSite());
                    }
                } elseif (!is_null($preferenceEntity) && is_null($fieldValue)) {
                    $this->em()->remove($preferenceEntity);
                    continue;
                } elseif (is_null($preferenceEntity) && is_null($fieldValue)) {
                    continue;
                }


                if ($fieldType == PreferenceFieldType::String || $fieldType == PreferenceFieldType::Number) {
                    $preferenceEntity->setValue($fieldValue);
                }
                if ($fieldType == PreferenceFieldType::Content) {
                    $preferenceEntity->setValue($fieldValue);
                }
                if ($fieldType == PreferenceFieldType::Date) {
                    $dateString = $fieldValue->format('Y-m-d');
                    $preferenceEntity->setValue($dateString);
                }
                if ($fieldType == PreferenceFieldType::Time) {
                    $dateString = $fieldValue->format('H:i:sO');
                    $preferenceEntity->setValue($dateString);
                }
                if ($fieldType == PreferenceFieldType::DateTime) {
                    $dateString = $fieldValue->format(\DateTime::ISO8601);
                    $preferenceEntity->setValue($dateString);
                }
                if ($fieldType == PreferenceFieldType::Serialized) {
                    $cleanedData = $this->prepareSerializedMeta($fieldValue);
                    $serializedString = $this->getSerializer()->serialize($cleanedData, 'json');
                    $preferenceEntity->setValue($serializedString);
                }
                $this->em()->persist($preferenceEntity);
            }
            $this->em()->flush();
        }
    }

    private function prepareSerializedMeta($value)
    {
        $returnValue = array();
        $value = $this->cleanArray($value);
        $sort = 0;
        foreach ($value as $key => $value) {
            $value['__sort__'] = $sort++;
            $returnValue[] = $value;
        }
        return $returnValue;
    }

    private function cleanArray($haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->cleanArray($haystack[$key]);
            }
            if (empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }
        return $haystack;
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\PreferenceRepository
     */
    public function getPreferenceRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:PreferenceEntity');
    }

}