<?php
namespace Bellwether\BWCMSBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity(repositoryClass="Bellwether\BWCMSBundle\Entity\AuditRepository")
 * @ORM\Table(name="BWAudit")
 */
class AuditEntity
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
    private $level;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $remoteAddress;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $logDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $module;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $guid;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $action;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Bellwether\BWCMSBundle\Entity\UserEntity")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", nullable=true)
     */
    private $user;

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
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    /**
     * @param mixed $remoteAddress
     */
    public function setRemoteAddress($remoteAddress)
    {
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @return mixed
     */
    public function getLogDate()
    {
        return $this->logDate;
    }

    /**
     * @param mixed $logDate
     */
    public function setLogDate($logDate)
    {
        $this->logDate = $logDate;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return mixed
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @param mixed $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }




}