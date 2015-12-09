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

class GeneralType Extends PreferenceType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    protected function buildFields()
    {
        $this->addField('title', PreferenceFieldType::String);
        $this->addField('description', PreferenceFieldType::String);
        $this->addField('keywords', PreferenceFieldType::String);
        $this->addField('adminEmail', PreferenceFieldType::String, true);
        $this->addField('adminEmail2', PreferenceFieldType::String, true);
        $this->addField('adminEmail3', PreferenceFieldType::String, true);
        $this->addField('adminEmail4', PreferenceFieldType::String, true);

        $this->addField('headerNavigation', PreferenceFieldType::Content);
        $this->addField('footerNavigation', PreferenceFieldType::Content);
        $this->addField('googleAnalyticsCode', PreferenceFieldType::String);

    }

    protected function buildForm()
    {
        $this->fb()->add('title', 'text',
            array(
                'label' => 'Title',
                'attr' => array(
                    'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                )
            )
        );
        $this->fb()->add('description', 'text',
            array(
                'label' => 'Description',
                'required' => false,
                'attr' => array(
                    'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                )
            )
        );
        $this->fb()->add('keywords', 'text',
            array(
                'label' => 'Keywords',
                'required' => false,
                'attr' => array(
                    'dir' => $this->sm()->getAdminCurrentSite()->getDirection()
                )
            )
        );

        $this->fb()->add('adminEmail', 'text',
            array(
                'label' => 'Admin Email'
            )
        );

        $this->fb()->add('adminEmail2', 'text',
            array(
                'required' => false,
                'label' => 'Admin Email 2'
            )
        );

        $this->fb()->add('adminEmail3', 'text',
            array(
                'required' => false,
                'label' => 'Admin Email 3'
            )
        );

        $this->fb()->add('adminEmail4', 'text',
            array(
                'required' => false,
                'label' => 'Admin Email 4'
            )
        );

        $this->fb()->add('headerNavigation', 'bwcms_content',
            array(
                'required' => false,
                'label' => 'Header Navigation',
                'contentType' => 'Navigation',
                'schema' => 'Folder'
            )
        );

        $this->fb()->add('footerNavigation', 'bwcms_content',
            array(
                'required' => false,
                'label' => 'Footer Navigation',
                'contentType' => 'Navigation',
                'schema' => 'Folder'
            )
        );

        $this->fb()->add('googleAnalyticsCode', 'text',
            array(
                'required' => false,
                'label' => 'Google Analytics'
            )
        );
    }

    function validateForm(FormEvent $event)
    {

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