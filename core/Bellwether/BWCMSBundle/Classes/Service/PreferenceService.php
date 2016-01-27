<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceType;
use Bellwether\BWCMSBundle\Classes\Base\PreferenceTypeInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;

use Bellwether\BWCMSBundle\Classes\Constants\AuditLevelType;
use Bellwether\BWCMSBundle\Classes\Constants\AuditActionType;

use Bellwether\BWCMSBundle\Classes\Preference\Type\GeneralType;
use Bellwether\BWCMSBundle\Classes\Preference\Type\EmailSMTPType;
use Bellwether\BWCMSBundle\Classes\Preference\Type\AmazonS3Type;

class PreferenceService extends BaseService
{

    private $preferenceType = array();

    private $loadedPreference = array();


    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function init()
    {
        if (!$this->loaded) {
            $this->addDefaultOptionTypes();
        }
        $this->loaded = true;
    }

    /**
     * @return PreferenceService
     */
    public function getManager()
    {
        return $this;
    }


    private function addDefaultOptionTypes()
    {
        $this->registerOptionType(new GeneralType($this->container, $this->requestStack));
        $this->registerOptionType(new EmailSMTPType($this->container, $this->requestStack));
        $this->registerOptionType(new AmazonS3Type($this->container, $this->requestStack));

        //Call other Preference Types
        $this->getEventDispatcher()->dispatch('BWCMS.Preference.Register');
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

    public function getAllPreferenceByType($type)
    {
        if (isset($this->loadedPreference[$type]) && !empty($this->loadedPreference[$type])) {
            return $this->loadedPreference[$type];
        }

        $classInstance = $this->getPreferenceClass($type);
        $cacheHash = 'Preference-' . $type;
        $preferenceResult = $this->cache()->fetch($cacheHash);
        if ($preferenceResult === false) {
            if ($this->admin()->isAdmin()) {
                $currentSite = $this->sm()->getAdminCurrentSite();
            } else {
                $currentSite = $this->sm()->getCurrentSite();
            }
            $qb = $this->getPreferenceRepository()->createQueryBuilder('p');
            $qb->andWhere(" ( p.type = '" . $classInstance->getType() . "' ) ");
            $qb->andWhere(" ( p.site = '" . $currentSite->getId() . "' OR p.site IS NULL ) ");
            $preferenceResult = $qb->getQuery()->getResult();
            $this->cache()->save($cacheHash, $preferenceResult);
        }

        $returnArray = array();
        if (!empty($preferenceResult)) {
            foreach ($preferenceResult as $preferenceEntity) {
                /**
                 * @var PreferenceEntity $preferenceEntity
                 */
                $prefField = $preferenceEntity->getField();
                $prefValue = $preferenceEntity->getValue();
                $prefType = $preferenceEntity->getFieldType();
                $prefValue = $this->decodeDataFromDB($prefType, $prefValue, $classInstance);
                if ($prefType == PreferenceFieldType::Content) {
                    if (!is_array($prefValue) && !is_null($prefValue)) {
                        $prefValue = $this->cm()->getContentRepository()->find($prefValue);
                    }
                }
                if ($prefType == PreferenceFieldType::Custom) {
                    $prefValue = $classInstance->loadCustomField($prefField, $prefValue);
                }
                $returnArray[$prefField] = $prefValue;
            }
            $fields = $classInstance->getFields();
            $fieldsNames = array_keys($fields);
            if (!empty($fieldsNames)) {
                foreach ($fieldsNames as $fieldName) {
                    if (!isset($returnArray[$fieldName])) {
                        $returnArray[$fieldName] = null;
                    }
                }
                $returnArray = array_intersect_key($returnArray, $fields);
            }
        }
        $this->loadedPreference[$classInstance->getType()] = $returnArray;
        return $returnArray;
    }

    public function getPreference($type, $field = false, $default = false)
    {
        $allPreference = $this->getAllPreferenceByType($type);
        if (empty($allPreference)) {
            return $default;
        }
        if (empty($field)) {
            return $allPreference;
        }
        if (isset($allPreference[$field])) {
            return $allPreference[$field];
        }
        return $default;
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
                    $criteria['site'] = $this->sm()->getAdminCurrentSite()->getId();
                } else {
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
                $fieldValue = $this->decodeDataFromDB($fieldType, $preferenceEntity->getValue(), $classInstance);
                $formField->setData($fieldValue);
            }
        }
//        $form = $classInstance->loadFormData($content, $form);
        return $form;
    }


    private function decodeDataFromDB($fieldType, $fieldValue, PreferenceTypeInterface $classInstance)
    {
        if (is_null($fieldValue)) {
            return null;
        }
        if ($fieldType == PreferenceFieldType::String || $fieldValue == PreferenceFieldType::Number) {
            return $fieldValue;
        }
        if ($fieldType == PreferenceFieldType::Content) {
            return $fieldValue;
        }
        if ($fieldType == PreferenceFieldType::Date || $fieldType == PreferenceFieldType::Time || $fieldType == PreferenceFieldType::DateTime) {
            $dateValue = new \DateTime($fieldValue);
            return $dateValue;
        }
        if ($fieldType == PreferenceFieldType::Serialized || $fieldType == PreferenceFieldType::Custom) {
            try {
                $data = $this->getSerializer()->deserialize($fieldValue, 'ArrayCollection', 'json');
            } catch (\RuntimeException $exp) {
                $data = array();
            }
            $data = $this->loadSerializedData($data);
            return $data;
        }
        return $fieldValue;
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
                    $criteria['site'] = $this->sm()->getAdminCurrentSite()->getId();
                } else {
                    $criteria['site'] = null;
                }

                /**
                 * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
                 */
                $action = 'UPDATE';
                $preferenceEntity = $preferenceRepo->findOneBy($criteria);
                if (is_null($preferenceEntity) && !is_null($fieldValue)) {
                    $preferenceEntity = new PreferenceEntity();
                    $preferenceEntity->setType($classInstance->getType());
                    $preferenceEntity->setField($fieldName);
                    $preferenceEntity->setFieldType($fieldType);
                    if (!$fieldInfo['global']) {
                        $preferenceEntity->setSite($this->sm()->getAdminCurrentSite());
                    }
                    $action = 'NEW';
                } elseif (!is_null($preferenceEntity) && (is_null($fieldValue) || empty($fieldValue))) {
                    $this->em()->remove($preferenceEntity);
                    $this->em()->flush();
                    $this->admin()->addAudit(AuditLevelType::Critical, 'Pref::' . $classInstance->getType() . '::' . $preferenceEntity->getField(), AuditActionType::Delete, $preferenceEntity->getId(), 'Deleted: ' . $preferenceEntity->getField());
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
                if ($fieldType == PreferenceFieldType::Serialized || $fieldType == PreferenceFieldType::Custom) {
                    $cleanedData = $this->prepareSerializedMeta($fieldValue);
                    $serializedString = $this->getSerializer()->serialize($cleanedData, 'json');
                    $preferenceEntity->setValue($serializedString);
                }
                $this->em()->persist($preferenceEntity);
                $this->em()->flush();

                if ($action == 'NEW') {
                    $this->admin()->addAudit(AuditLevelType::Normal, 'Pref::' . $classInstance->getType() . '::' . $preferenceEntity->getField(), AuditActionType::Add, $preferenceEntity->getId(), 'Added: ' . $preferenceEntity->getField());
                } else if ($action == 'UPDATE') {
                    $this->admin()->addAudit(AuditLevelType::Normal, 'Pref::' . $classInstance->getType() . '::' . $preferenceEntity->getField(), AuditActionType::Edit, $preferenceEntity->getId(), 'Edited: ' . $preferenceEntity->getField());
                }
            }
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

    public function getSinglePreference($fieldName, $fieldScope = '_SINGLE_PREFERENCE_', SiteEntity $siteEntity = null)
    {
        $criteria = array(
            'field' => $fieldName,
            'fieldType' => PreferenceFieldType::Internal,
            'type' => $fieldScope,
            'site' => $siteEntity
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (!is_null($preferenceEntity)) {
            return $preferenceEntity->getValue();
        }
        return null;
    }

    public function saveSinglePreference($fieldName, $fieldValue = null, $fieldScope = '_SINGLE_PREFERENCE_', SiteEntity $siteEntity = null)
    {
        $criteria = array(
            'field' => $fieldName,
            'fieldType' => PreferenceFieldType::Internal,
            'type' => $fieldScope,
            'site' => $siteEntity
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (!is_null($preferenceEntity) && is_null($fieldValue)) {
            $this->em()->remove($preferenceEntity);
            $this->em()->flush();
            return true;
        }
        if (is_null($preferenceEntity)) {
            $preferenceEntity = new PreferenceEntity();
            $preferenceEntity->setField($fieldName);
            $preferenceEntity->setType($fieldScope);
            $preferenceEntity->setFieldType(PreferenceFieldType::Internal);
            $preferenceEntity->setSite($siteEntity);
        }
        $preferenceEntity->setValue($fieldValue);
        $this->em()->persist($preferenceEntity);
        $this->em()->flush();
        return true;
    }


    /**
     * @return \Bellwether\BWCMSBundle\Entity\PreferenceRepository
     */
    public function getPreferenceRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:PreferenceEntity');
    }

}
