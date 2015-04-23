<?php

namespace Bellwether\BWCMSBundle\Classes\Preference\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceType;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Bellwether\BWCMSBundle\Classes\Preference\Form\SampleForm;

class EmailSMTPType Extends PreferenceType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    protected function buildFields()
    {
//        $this->addField('transport', PreferenceFieldType::String, true);
        $this->addField('username', PreferenceFieldType::String, true);
        $this->addField('password', PreferenceFieldType::String, true);
        $this->addField('host', PreferenceFieldType::String, true);
        $this->addField('port', PreferenceFieldType::String, true);
        $this->addField('encryption', PreferenceFieldType::String, true);
        $this->addField('auth_mode', PreferenceFieldType::String, true);
        $this->addField('sender_address', PreferenceFieldType::String, true);
        $this->addField('delivery_address', PreferenceFieldType::String, true);
    }

    protected function buildForm()
    {
//        $this->fb()->add('transport', 'choice',
//            array(
//                'label' => 'Transport',
//                'choices' => array('smtp' => 'SMTP', 'gmail' => 'Gmail'),
//            )
//        );
        $this->fb()->add('username', 'text',
            array(
                'label' => 'Username'
            )
        );
        $this->fb()->add('password', 'text',
            array(
                'label' => 'Password'
            )
        );
        $this->fb()->add('host', 'text',
            array(
                'label' => 'Hostname'
            )
        );
        $this->fb()->add('port', 'text',
            array(
                'label' => 'Port'
            )
        );
        $this->fb()->add('encryption', 'choice',
            array(
                'label' => 'Encryption',
                'choices' => array(null => 'none', 'tls' => 'TLS', 'ssl' => 'SSL'),
            )
        );
        $this->fb()->add('auth_mode', 'choice',
            array(
                'label' => 'Auth Mode',
                'choices' => array(null => 'none', 'plain' => 'Plain', 'login' => 'Login', 'cram-md5' => 'Cram MD5'),
            )
        );
        $this->fb()->add('sender_address', 'text',
            array(
                'label' => 'Sender Email Address'
            )
        );
        $this->fb()->add('delivery_address', 'text',
            array(
                'label' => 'Delivery Email Address'
            )
        );
    }

    function validateForm(FormEvent $event)
    {

    }

    public function getType()
    {
        return 'Email.SMTP';
    }

    public function getName()
    {
        return "Email SMTP";
    }

}