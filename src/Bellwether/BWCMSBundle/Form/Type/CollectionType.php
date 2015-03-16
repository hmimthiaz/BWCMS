<?php

namespace Bellwether\BWCMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    public function getParent()
    {
        return 'collection';
    }

    public function getName()
    {
        return 'bwcms_collection';
    }
}
