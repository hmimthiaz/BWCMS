<?php
namespace Bellwether\BWCMSBundle\Entity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\SearchEntityRepository")
 * @ORM\Table(name="BWSearch")
 */
class SearchEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $indexedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\SiteEntity",cascade={"remove"})
     * @ORM\JoinColumn(name="siteId", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity", cascade={"remove"})
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id", nullable=false)
     */
    private $content;



}