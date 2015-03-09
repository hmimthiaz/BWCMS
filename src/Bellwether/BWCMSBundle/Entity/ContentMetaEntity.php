<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentMeta
 *
 * 
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentMetaRepository")
 * @ORM\Table(name="BWContentMeta")
 */
class ContentMetaEntity
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="guid",name="id")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $key;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity", inversedBy="meta")
     * @ORM\JoinColumn(name="content_id", referencedColumnName="id", nullable=false)
     */
    private $content;

}
