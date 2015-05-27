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

class ResetPasswordType extends AbstractType
{
    /**
     * @var UserEntity
     */
    private $existingUser;
    private $password;

    function __construct(UserEntity $existingUser, $password)
    {
        $this->existingUser = $existingUser;
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
                'read_only' => true,
                'data' => $this->existingUser->getFirstName()
            )
        );

        $builder->add('lastName', 'text',
            array(
                'label' => 'Last Name',
                'read_only' => true,
                'data' => $this->existingUser->getLastName()
            )
        );

        $builder->add('email', 'email',
            array(
                'label' => 'Email',
                'read_only' => true,
                'data' => $this->existingUser->getEmail()
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
