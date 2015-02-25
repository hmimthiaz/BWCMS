<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content
 *
 * 
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentRepository")
 */
class Content
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", unique=true, length=100, nullable=true)
     */
    private $guid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expire_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publish_date;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\Site", inversedBy="content")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\User", inversedBy="content")
     * @ORM\JoinColumn(name="author", referencedColumnName="id", nullable=false)
     */
    private $user;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
