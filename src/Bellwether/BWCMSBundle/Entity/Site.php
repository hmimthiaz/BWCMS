<?php
namespace Bellwether\BWCMSBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\SiteRepository")
 * @ORM\Table(name="BWSite")
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $direction;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=true)
     */
    private $domain;

}