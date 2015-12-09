<?php

namespace Bellwether\BWCMSBundle\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class ForgotType extends AbstractType
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

        $builder->add('email', 'email',
            array(
                'label' => 'Email',
                'label_attr' => array(
                    'class' => 'label-wrapper'
                ),
                'constraints' => array(
                    new NotBlank(),
                    new Email(),
                )
            )
        );

        $builder->add('captcha', 'captcha',
            array(
                'label_attr' => array(
                    'class' => 'label-wrapper'
                )
            )
        );

        $builder->add('submit', 'submit',
            array(
                'label' => 'Request Password',
                'attr' => array(
                    'class' => 'login-btn'
                )
            )
        );

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
        return 'bwcms_forgot';
    }
}
