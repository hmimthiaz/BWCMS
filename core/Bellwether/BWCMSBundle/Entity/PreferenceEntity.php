<?php
namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\PreferenceRepository")
 * @ORM\Table(name="BWPreference")
 */
class PreferenceEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $fieldType;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $field;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\SiteEntity",cascade={"remove"})
     * @ORM\JoinColumn(name="siteId", referencedColumnName="id", nullable=true)
     */
    private $site;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    /**
     * @param mixed $fieldType
     */
    public function setFieldType($fieldType)
    {
        $this->fieldType = $fieldType;
    }


    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return SiteEntity
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param SiteEntity $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }
}