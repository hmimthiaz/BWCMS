<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Entity\SiteRepository;

class MailService extends BaseService
{

    protected $transport = null;

    protected $mailer = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SiteService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        if ($this->mailer == null) {
            $this->mailer = \Swift_Mailer::newInstance($this->getTransport());
        }
        return $this->mailer;
    }

    /**
     * @return \Swift_SmtpTransport
     */
    public function getTransport()
    {
        if ($this->transport == null) {
            $this->transport = \Swift_SmtpTransport::newInstance();

            $this->transport->setHost('');

        }
        return $this->transport;
    }


}