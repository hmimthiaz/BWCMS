<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Type;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Content\BaseContentType;
use Bellwether\BWCMSBundle\Classes\Content\ContentFieldType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class FolderContentType Extends BaseContentType
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function buildFields()
    {
        $this->addField('title', ContentFieldType::String);
        $this->addField('summary', ContentFieldType::String);
        $this->addField('content', ContentFieldType::String);
    }


    public function buildForm()
    {
        $this->fb()->add('title', 'text',
            array(
                'max_length' => 100,
                'required' => true,
                'label' => 'Title',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            )
        );

        $this->fb()->add('summary', 'textarea',
            array(
                'max_length' => 100,
                'required' => false,
                'label' => 'Summary'
            )
        );

        $this->fb()->add('content', 'textarea',
            array(
                'max_length' => 100,
                'required' => false,
                'label' => 'Content'
            )
        );
    }

    public function getType()
    {
        return "Folder";
    }

    public function getSchema()
    {
        return "Default";
    }

    public function getName()
    {
        return "Folder";
    }

}
