<?php

namespace Bellwether\BWCMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NewUserEntityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('firstName','text',
            array(
                'max_length'=>100,
                'required' => true,
                'label' => 'First Name'
            )
        );

        $builder->add('lastName','text',
            array(
                'max_length'=>100,
                'required' => false,
                'label' => 'Last Name'
            )
        );

        $builder->add('email','email',
            array(
                'max_length'=>100,
                'required' => true,
                'label' => 'Email'
            )
        );

        $builder->add('mobile','text',
            array(
                'max_length'=>20,
                'required' => false,
                'label' => 'Mobile'
            )
        );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bellwether\BWCMSBundle\Entity\UserEntity'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bellwether_bwcmsbundle_userentity';
    }
}
