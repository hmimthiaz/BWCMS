<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentMeta
 *
 * 
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentRelationRepository")
 * @ORM\Table(name="BWContentRelation")
 */
class ContentRelationEntity
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="guid", name="id")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $relation;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity", inversedBy="relation")
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id", nullable=false)
     */
    private $content;

    /**
     * @ORM\Column(type="guid", name="relatedContentId")
     */
    private $relatedContent;


}
