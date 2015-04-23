<?php

namespace Bellwether\BWCMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentEntityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expireDate')
            ->add('publishDate')
            ->add('treeRoot')
            ->add('treeRight')
            ->add('treeLevel')
            ->add('treeLeft')
            ->add('title')
            ->add('summary')
            ->add('content')
            ->add('name')
            ->add('type')
            ->add('schema')
            ->add('mime')
            ->add('extension')
            ->add('size')
            ->add('height')
            ->add('width')
            ->add('modifiedDate')
            ->add('createdDate')
            ->add('treeParent')
            ->add('author')
            ->add('site')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bellwether\BWCMSBundle\Entity\ContentEntity'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bellwether_bwcmsbundle_contententity';
    }
}
