<?php

namespace Bellwether\BWCMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentTemplateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = $options['choices'];
        if (!isset($view->vars['value']) || empty($view->vars['value'])) {
            reset($choices);
            $item = current($choices);
            $view->vars['value'] = $item['template'];
        }
        $view->vars['choices'] = $choices;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(),
            'required' => false,
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'bwcms_content_template';
    }
}
