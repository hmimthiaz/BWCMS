<?php

namespace Bellwether\BWCMSBundle\Classes\Preference\Type;


use Bellwether\BWCMSBundle\Classes\Preference\PreferenceTypeInterface;
use Bellwether\BWCMSBundle\Classes\Preference\PreferenceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Constants\ContentFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;


class GeneralType Extends PreferenceType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    protected function buildFields()
    {
        // TODO: Implement buildFields() method.
    }

    protected function buildForm()
    {
        // TODO: Implement buildForm() method.
    }

    function validateForm(FormEvent $event)
    {
        // TODO: Implement validateForm() method.
    }

    public function getType()
    {
        return 'General';
    }

    public function getName()
    {
        return "General";
    }

}