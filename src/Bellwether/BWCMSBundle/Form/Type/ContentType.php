<?php

namespace Bellwether\BWCMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContentType extends AbstractType
{
    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $holder = $view->vars['id'] . 'Holder';
        $view->vars['holderId'] = $holder;

        $view->vars['browserURL'] = $this->container->get('router')->generate('content_browser', array('holder' => $holder), true);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {


        $resolver->setDefaults(array(
            'contentType' => 'content',
            'required' => false,
            'compound' => false,
        ));
    }


    public function getName()
    {
        return 'bwcms_content';
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

}
