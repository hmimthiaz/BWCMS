<?php
namespace Bellwether\BWCMSBundle\Entity;


use Doctrine\ORM\Mapping AS ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\ContentRepository")
 * @ORM\Table(name="BWContent")
 */
class ContentEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expireDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishDate;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="guid", nullable=true, name="treeRoot")
     */
    private $treeRoot;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer", nullable=true, name="treeRight")
     */
    private $treeRight;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer", nullable=true, name="treeLevel")
     */
    private $treeLevel;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer", nullable=true, name="treeLeft")
     */
    private $treeLeft;

    /**
     * @ORM\OneToMany(targetEntity="Bellwether\BWCMSBundle\Entity\Content", mappedBy="treeParent")
     * @ORM\OrderBy({"lft"="ASC"})
     */
    private $children;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\Content", inversedBy="children")
     * @ORM\JoinColumn(name="treeParentId", referencedColumnName="id", onDelete="CASCADE")
     */
    private $treeParent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $mime;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $modifiedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\User")
     * @ORM\JoinColumn(name="author", referencedColumnName="id", nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", nullable=false)
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
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * @param mixed $expireDate
     */
    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
    }

    /**
     * @return mixed
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param mixed $publishDate
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;
    }

    /**
     * @return mixed
     */
    public function getTreeRoot()
    {
        return $this->treeRoot;
    }

    /**
     * @param mixed $treeRoot
     */
    public function setTreeRoot($treeRoot)
    {
        $this->treeRoot = $treeRoot;
    }

    /**
     * @return mixed
     */
    public function getTreeRight()
    {
        return $this->treeRight;
    }

    /**
     * @param mixed $treeRight
     */
    public function setTreeRight($treeRight)
    {
        $this->treeRight = $treeRight;
    }

    /**
     * @return mixed
     */
    public function getTreeLevel()
    {
        return $this->treeLevel;
    }

    /**
     * @param mixed $treeLevel
     */
    public function setTreeLevel($treeLevel)
    {
        $this->treeLevel = $treeLevel;
    }

    /**
     * @return mixed
     */
    public function getTreeLeft()
    {
        return $this->treeLeft;
    }

    /**
     * @param mixed $treeLeft
     */
    public function setTreeLeft($treeLeft)
    {
        $this->treeLeft = $treeLeft;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed
     */
    public function getTreeParent()
    {
        return $this->treeParent;
    }

    /**
     * @param mixed $treeParent
     */
    public function setTreeParent($treeParent)
    {
        $this->treeParent = $treeParent;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param mixed $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * @param mixed $modifiedDate
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
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

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
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