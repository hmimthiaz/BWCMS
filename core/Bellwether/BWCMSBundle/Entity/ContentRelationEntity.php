<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentMeta
 *
 *
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentRelationRepository")
 * @ORM\Table(name="BWContentRelation",
 *      uniqueConstraints={
 *         @ORM\UniqueConstraint(name="ix_contentId_relation_relatedContentId", columns={"contentId","relation","relatedContentId"}),
 *     }
 *  )
 **/
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
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity", inversedBy="relation",cascade={"remove"})
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id", nullable=false)
     */
    private $content;


    /**
     * @ORM\Column(name="relation",type="string", length=100, nullable=false)
     */
    private $relation;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity")
     * @ORM\JoinColumn(name="relatedContentId", referencedColumnName="id",nullable=false)
     **/
    private $relatedContent;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * @param mixed $relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;
    }

    /**
     * @return ContentEntity
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param ContentEntity $content
     */
    public function setContent(ContentEntity $content)
    {
        $this->content = $content;
    }

    /**
     * @return ContentEntity
     */
    public function getRelatedContent()
    {
        return $this->relatedContent;
    }

    /**
     * @param ContentEntity $relatedContent
     */
    public function setRelatedContent(ContentEntity $relatedContent)
    {
        $this->relatedContent = $relatedContent;
    }


}
