<?php
namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="BWGuidReference")
 */
class GuidReferenceEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $sourceSiteGUID;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $targetSiteGUID;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $sourceGUID;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $targetGUID;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdDate;

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
    public function getSourceSiteGUID()
    {
        return $this->sourceSiteGUID;
    }

    /**
     * @param mixed $sourceSiteGUID
     */
    public function setSourceSiteGUID($sourceSiteGUID)
    {
        $this->sourceSiteGUID = $sourceSiteGUID;
    }

    /**
     * @return mixed
     */
    public function getTargetSiteGUID()
    {
        return $this->targetSiteGUID;
    }

    /**
     * @param mixed $targetSiteGUID
     */
    public function setTargetSiteGUID($targetSiteGUID)
    {
        $this->targetSiteGUID = $targetSiteGUID;
    }

    /**
     * @return mixed
     */
    public function getSourceGUID()
    {
        return $this->sourceGUID;
    }

    /**
     * @param mixed $sourceGUID
     */
    public function setSourceGUID($sourceGUID)
    {
        $this->sourceGUID = $sourceGUID;
    }

    /**
     * @return mixed
     */
    public function getTargetGUID()
    {
        return $this->targetGUID;
    }

    /**
     * @param mixed $targetGUID
     */
    public function setTargetGUID($targetGUID)
    {
        $this->targetGUID = $targetGUID;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param mixed $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }


}