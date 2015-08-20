<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\LocaleEntityRepository")
 * @ORM\Table(name="BWLocale")
 */
class LocaleEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $hash;

    /**
     * @ORM\Column(type="text", name="sourceText", nullable=false)
     */
    private $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\SiteEntity",cascade={"persist"})
     * @ORM\JoinColumn(name="siteId", referencedColumnName="id", nullable=false)
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
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
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
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }


}
