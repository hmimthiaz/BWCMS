<?php

namespace Bellwether\BWCMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ThumbStyleType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('mode', 'choice',
                array(
                    'label' => 'Mode',
                    'choices' => array(
                        'resize' => 'Resize',
                        'scaleResize' => 'Scale Resize',
                        'forceResize' => 'Force Resize',
                        'cropResize' => 'Crop Resize',
                        'zoomCrop' => 'Zoom Crop'
                    ),
                    'data' => 'scaleResize',
                )
            )
            ->add('width', 'text',
                array(
                    'label' => 'Width',
                    'data' => '100'
                )
            )
            ->add('height', 'text',
                array(
                    'label' => 'Height',
                    'data' => '100'
                )
            )
            ->add('background')
            ->add('quality', 'text',
                array(
                    'label' => 'Quality',
                    'data' => '80'
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bellwether\BWCMSBundle\Entity\ThumbStyle'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bellwether_bwcmsbundle_thumbstyle';
    }
}
