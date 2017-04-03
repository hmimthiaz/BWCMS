<?php

namespace Bellwether\BWCMSBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Entity\ContentRepository;
use Bellwether\BWCMSBundle\Entity\ContentEntity;

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

        $value = $view->vars['value'];
        $view->vars['selectedText'] = '';
        $view->vars['selectedThumb'] = '';
        if (!empty($value)) {
            $cr = $this->cm()->getContentRepository();
            $content = $cr->find($value);
            if (!empty($content)) {
                $view->vars['selectedText'] = $content->getTitle();
                $view->vars['selectedThumb'] = $this->mm()->getContentThumbURL($content, 32, 32);
            }
        }
        $browserURLOptions = array();
        $browserURLOptions['holder'] = $holder;
        if (isset($options['contentType']) && !empty($options['contentType'])) {
            $browserURLOptions['type'] = $options['contentType'];
        }
        if (isset($options['schema']) && !empty($options['schema'])) {
            $browserURLOptions['schema'] = $options['schema'];
        }
        if (isset($options['onlyImage']) && $options['onlyImage'] === true) {
            $browserURLOptions['onlyImage'] = 'true';
        }
        if (isset($options['extension']) && !empty($options['extension'])) {
            $browserURLOptions['extension'] = $options['extension'];
        }
        $view->vars['browserURL'] = $this->generateUrl('_bwcms_admin_content_browser', $browserURLOptions, true);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'contentType' => 'Content',
            'schema' => false,
            'onlyImage' => false,
            'required' => false,
            'extension' => false,
            'compound' => false,
        ));
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string $route The name of the route
     * @param mixed $parameters An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->container->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
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
