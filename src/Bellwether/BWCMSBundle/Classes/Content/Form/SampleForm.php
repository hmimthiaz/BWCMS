<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SampleForm extends AbstractType
{
    function __construct()
    {

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('desc', 'text');
        $builder->add('age', 'text');
        $builder->add('item', 'bwcms_content');
        $builder->add('content', 'textarea',
            array(
                'required' => false,
                'label' => 'Content',
                'attr' => array(
                    'class' => 'editor'
                )
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Sample_Form';
    }
}
