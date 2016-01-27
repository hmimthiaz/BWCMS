<?php
namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Proxies\__CG__\Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;
use Proxies\__CG__\BWDigital\BWSerkalBundle\Entity\ContactEntity;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\S3QueueRepository")
 * @ORM\Table(name="BWS3Queue")
 */
class S3QueueEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $prefix;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="float")
     */
    private $thumbScale;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $uploadedDate;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\SiteEntity")
     * @ORM\JoinColumn(name="siteId", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity")
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id", nullable=false)
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ThumbStyleEntity")
     * @ORM\JoinColumn(name="thumbStyleId", referencedColumnName="id", nullable=false)
     */
    private $thumStyle;

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
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return float
     */
    public function getThumbScale()
    {
        return $this->thumbScale;
    }

    /**
     * @param float $thumbScale
     */
    public function setThumbScale($thumbScale)
    {
        $this->thumbScale = $thumbScale;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getUploadedDate()
    {
        return $this->uploadedDate;
    }

    /**
     * @param \DateTime $uploadedDate
     */
    public function setUploadedDate($uploadedDate)
    {
        $this->uploadedDate = $uploadedDate;
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

    /**
     * @return ContactEntity
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param ContactEntity $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return ThumbStyleEntity
     */
    public function getThumStyle()
    {
        return $this->thumStyle;
    }

    /**
     * @param ThumbStyleEntity $thumStyle
     */
    public function setThumStyle($thumStyle)
    {
        $this->thumStyle = $thumStyle;
    }

}