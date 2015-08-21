<?php

namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentMeta
 *
 * 
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentMediaRepository")
 * @ORM\Table(name="BWContentMedia")
 */
class ContentMediaEntity
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
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\ContentEntity", inversedBy="media",cascade={"remove"})
     * @ORM\JoinColumn(name="contentId", referencedColumnName="id", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mime;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $extension;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $data;

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
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param mixed $mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

}
