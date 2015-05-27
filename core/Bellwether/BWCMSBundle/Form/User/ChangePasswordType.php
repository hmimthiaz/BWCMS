<?php

namespace Bellwether\BWCMSBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Count;
use Bellwether\BWCMSBundle\Entity\UserEntity;

class ChangePasswordType extends AbstractType
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

        $builder->add('oldpassword', 'password',
            array(
                'label' => 'Old Password',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 8, 'max' => 15))
                )
            )
        );

        $builder->add('password', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'options' => array('attr' => array('class' => 'password-field')),
            'required' => true,
            'first_options' => array(
                'label' => 'Password',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 8, 'max' => 15))
                ),
            ),
            'second_options' => array(
                'label' => 'Repeat Password',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 8, 'max' => 15))
                ),

            ),
        ));

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bellwether_bwcmsbundle_userentity';
    }
}
