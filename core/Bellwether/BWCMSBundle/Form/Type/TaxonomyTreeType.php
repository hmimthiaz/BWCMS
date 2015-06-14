<?php

namespace Bellwether\BWCMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaxonomyTreeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['holderId'] = $view->vars['id'] . 'Tree';
        $nodes = $options['nodes'];
        if (isset($view->vars['value']) && !empty($view->vars['value'])) {
            $values = $view->vars['value'];
            if (!empty($nodes)) {
                foreach ($nodes as $nodeIndex => $node) {
                    if (in_array($node['id'], $values)) {
                        $nodes[$nodeIndex]['state']['checked'] = true;
                    }
                }
            }
            $view->vars['value'] = join(',', $values);
        }
        $view->vars['nodes'] = json_encode($nodes);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'nodes' => array(),
            'required' => false,
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'bwcms_taxonomy_tree';
    }
}
