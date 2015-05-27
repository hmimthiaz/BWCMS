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
use Symfony\Component\Security\Core\Util\SecureRandom;

class NewType extends AbstractType
{
    private $roles;
    private $password;

    function __construct($roles, $password)
    {
        $this->roles = $roles;
        $this->password = $password;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('firstName', 'text',
            array(
                'label' => 'First Name',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            )
        );

        $builder->add('lastName', 'text',
            array(
                'label' => 'Last Name',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            )
        );

        $builder->add('email', 'email',
            array(
                'label' => 'Email',
                'constraints' => new Email()
            )
        );

        $builder->add('password', 'text',
            array(
                'label' => 'Password',
                'data' => $this->password,
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 8, 'max' => 15))
                )
            )
        );

        $builder->add('mobile', 'text',
            array(
                'max_length' => 20,
                'required' => false,
                'label' => 'Mobile',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3)),
                    new Regex(array('pattern' => '/[0-9]/', 'message' => 'Please enter only numbers and not any other characters.')),
                ),
            )
        );

        $builder->add('user_roles', 'choice',
            array(
                'choices' => $this->roles,
                'label' => 'Roles',
                'expanded' => true,
                'multiple' => true,
                'constraints' => new Count(array('min' => 1, 'minMessage' => 'You need to select minimum one role.'))
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
        return 'bellwether_bwcmsbundle_userentity';
    }
}
