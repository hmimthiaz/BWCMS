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

class ProfileType extends AbstractType
{
    /**
     * @var UserEntity
     */
    private $existingUser;

    function __construct(UserEntity $existingUser)
    {
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
                'read_only' => true,
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
