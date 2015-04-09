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

            $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
            /**
             *  'username' => string 'AKIAJ3EUV7MIOZ33MVWA' (length=20)
             * 'password' => string 'AiIqolUYybkRBJ43HrXeAsXUjgaBzFDi63RrZ9v1shwy' (length=44)
             * 'host' => string 'email-smtp.us-west-2.amazonaws.com' (length=34)
             * 'port' => string '25' (length=2)
             * 'encryption' => string 'tls' (length=3)
             * 'sender_address' => string 'imthi@dxb.io' (length=12)
             * 'auth_mode' => null
             * 'delivery_address' => null
             */

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
                $this->transport->setPassword($emailSettings['port']);
            }
            if (!is_null($emailSettings['encryption']) && !empty($emailSettings['encryption'])) {
                $this->transport->setEncryption($emailSettings['encryption']);
            }
            if (!is_null($emailSettings['auth_mode']) && !empty($emailSettings['auth_mode'])) {
                $this->transport->setAuthMode($emailSettings['auth_mode']);
            }
            if (!is_null($emailSettings['auth_mode']) && !empty($emailSettings['auth_mode'])) {
            }



        }
        return $this->transport;
    }


}