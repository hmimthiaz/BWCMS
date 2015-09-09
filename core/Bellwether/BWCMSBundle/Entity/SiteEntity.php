<?php
namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\SiteRepository")
 * @ORM\Table(name="BWSite")
 */
class SiteEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $direction;

    /**
     * @ORM\Column(type="string", unique=true, length=10, nullable=false)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", unique=true, length=100, nullable=true)
     */
    private $domain;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $skinFolderName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $adminColorThemeName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDefault;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getSkinFolderName()
    {
        return $this->skinFolderName;
    }

    /**
     * @param string $skinFolderName
     */
    public function setSkinFolderName($skinFolderName)
    {
        $this->skinFolderName = $skinFolderName;
    }

    /**
     * @return mixed
     */
    public function getAdminColorThemeName()
    {
        if (is_null($this->adminColorThemeName)) {
            return 'grey';
        }
        return $this->adminColorThemeName;
    }

    /**
     * @param mixed $adminColorThemeName
     */
    public function setAdminColorThemeName($adminColorThemeName)
    {
        $this->adminColorThemeName = $adminColorThemeName;
    }


    /**
     * @return mixed
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param mixed $isDefault
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;
    }


}