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
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $level;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $remoteAddress;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $logDate;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $module;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $guid;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
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


}