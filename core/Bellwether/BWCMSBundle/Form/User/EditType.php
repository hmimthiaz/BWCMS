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

class EditType extends AbstractType
{
    private $roles;

    /**
     * @var UserEntity
     */
    private $existingUser;

    function __construct($roles, UserEntity $existingUser)
    {
        $this->roles = $roles;
        $this->existingUser = $existingUser;
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
                'data' => $this->existingUser->getFirstName(),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            )
        );

        $builder->add('lastName', 'text',
            array(
                'label' => 'Last Name',
                'data' => $this->existingUser->getLastName(),
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3))
                )
            )
        );

        $builder->add('email', 'email',
            array(
                'label' => 'Email',
                'data' => $this->existingUser->getEmail(),
                'constraints' => array(
                    new NotBlank(),
                    new Email(),
                )
            )
        );

        $builder->add('mobile', 'text',
            array(
                'max_length' => 20,
                'data' => $this->existingUser->getMobile(),
                'required' => false,
                'label' => 'Mobile',
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('min' => 3)),
                    new Regex(array('pattern' => '/[0-9]/', 'message' => 'Please enter only numbers and not any other characters.')),
                ),
            )
        );

        $builder->add('company', 'text',
            array(
                'label' => 'Company',
                'data' => $this->existingUser->getCompany(),
                'required' => false,
            )
        );

        $builder->add('user_roles', 'choice',
            array(
                'choices' => $this->roles,
                'data' => $this->existingUser->getRoles(),
                'label' => 'Roles',
                'expanded' => true,
                'multiple' => true,
                'constraints' => new Count(array('min' => 1, 'minMessage' => 'You need to select minimum one role.'))
            )
        );

        $builder->add('locked', 'choice',
            array(
                'choices' => array(false => 'Not Locked', true => 'Locked'),
                'data' => $this->existingUser->isLocked(),
                'label' => 'Lock?',
                'required' => false,
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
