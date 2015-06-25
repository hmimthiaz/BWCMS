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

    protected $logger = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return MailService
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
     * @return \Swift_Plugins_Loggers_EchoLogger
     */
    public function enableLogger()
    {
        if ($this->logger == null) {
            $this->logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $this->getMailer()->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->logger));
        }
        return $this->logger;
    }

    /**
     * @return \Swift_SmtpTransport
     */
    public function getTransport()
    {
        if ($this->transport == null) {
            $this->transport = \Swift_SmtpTransport::newInstance();
            $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
            if (!is_null($emailSettings['host']) && !empty($emailSettings['host'])) {
                $this->transport->setHost($emailSettings['host']);
            }
            if (!is_null($emailSettings['username']) && !empty($emailSettings['username'])) {
                $this->transport->setUsername($emailSettings['username']);
            }
            if (!is_null($emailSettings['password']) && !empty($emailSettings['password'])) {
                $this->transport->setPassword($emailSettings['password']);
            }
            if (!is_null($emailSettings['port']) && !empty($emailSettings['port'])) {
                $this->transport->setPort($emailSettings['port']);
            }
            if (!is_null($emailSettings['encryption']) && !empty($emailSettings['encryption'])) {
                $this->transport->setEncryption($emailSettings['encryption']);
            }
            if (!is_null($emailSettings['auth_mode']) && !empty($emailSettings['auth_mode'])) {
                $this->transport->setAuthMode($emailSettings['auth_mode']);
            }
        }
        return $this->transport;
    }

}
