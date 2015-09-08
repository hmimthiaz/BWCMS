<?php

namespace Bellwether\BWCMSBundle\Classes\Content\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Bellwether\BWCMSBundle\Entity\ContentEntity;


class ContentEmptyForm extends AbstractType
{
    /**
     * @var ContentEntity
     */
    private $contentEntity = null;

    function __construct(ContentEntity $contentEntity = null)
    {
        $this->contentEntity = $contentEntity;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    }

    /**
     * @return ContentEntity
     */
    public function getContentEntity()
    {
        return $this->contentEntity;
    }

    /**
     * @param ContentEntity $contentEntity
     */
    public function setContentEntity($contentEntity)
    {
        $this->contentEntity = $contentEntity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'BWCF';
    }
}
