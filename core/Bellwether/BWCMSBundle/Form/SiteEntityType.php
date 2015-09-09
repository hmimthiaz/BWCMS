<?php

namespace Bellwether\BWCMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Bellwether\BWCMSBundle\Classes\Service\TemplateService;

class SiteEntityType extends AbstractType
{

    /**
     * @var TemplateService
     */
    private $templateService;

    function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('locale')
            ->add('direction', 'choice',
                array(
                    'label' => 'Direction',
                    'choices' => array('ltr' => 'Left to right', 'rtl' => 'Right to left'),
                )
            )
            ->add('slug')
            ->add('domain')
            ->add('adminColorThemeName', 'choice',
                array(
                    'label' => 'Admin Theme',
                    'choices' => array(
                        'grey' => 'Grey',
                        'blue' => 'Blue',
                    ),
                )
            )
            ->add('skinFolderName', 'choice',
                array(
                    'label' => 'Skin',
                    'choices' => $this->templateService->getSkins(),
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bellwether\BWCMSBundle\Entity\SiteEntity'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bellwether_bwcmsbundle_siteentity';
    }
}
