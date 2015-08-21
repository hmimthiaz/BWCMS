<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Entity\PreferenceRepository;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;


class SearchService extends BaseService
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SearchService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * Service Init.
     */
    public function init()
    {
        if (!$this->loaded) {

        }
        $this->loaded = true;
    }



    public function getLastIndexedDate()
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            return new \DateTime('@0');
        }
        return new \DateTime($preferenceEntity->getValue());
    }

    public function saveLastIndexDate(\DateTime $dateTime)
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            $preferenceEntity = new PreferenceEntity();
            $preferenceEntity->setField('_SEARCH_LIT_');
            $preferenceEntity->setType('_SEARCH_');
            $preferenceEntity->setFieldType(PreferenceFieldType::Internal);
            $preferenceEntity->setSite(null);
        }
        $dateString = $dateTime->format(\DateTime::ISO8601);
        $preferenceEntity->setValue($dateString);
        $this->em()->persist($preferenceEntity);
        $this->em()->flush();
        return true;
    }

}
