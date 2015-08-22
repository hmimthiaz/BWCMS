<?php
namespace Bellwether\BWCMSBundle\Classes\Preference;

use Bellwether\BWCMSBundle\Classes\Base\PreferenceTypeInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilder;
use Bellwether\BWCMSBundle\Classes\Preference\Form\PreferenceEmptyForm;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;

use Bellwether\BWCMSBundle\Classes\Service\SiteService;
use Bellwether\BWCMSBundle\Classes\Service\ContentService;
use Bellwether\BWCMSBundle\Classes\Service\LocaleService;
use Bellwether\BWCMSBundle\Classes\Service\MediaService;
use Bellwether\BWCMSBundle\Classes\Service\PreferenceService;
use Doctrine\ORM\EntityManager;



abstract class PreferenceType implements PreferenceTypeInterface
{
    /**
     * @var ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * @var RequestStack
     *
     * @api
     */
    protected $requestStack;


    /**
     * @var FormBuilder
     */
    private $formBuilder = null;

    /**
     * @var Form
     */
    private $form = null;

    private $fields = null;

    /**
     * @var bool
     */
    private $isPagePreference = false;

    /**
     * @return FormBuilder
     */
    final public function fb()
    {
        if ($this->formBuilder == null) {
            $contentEmptyForm = new PreferenceEmptyForm();
            $this->formBuilder = $this->container->get('form.factory')->createBuilder($contentEmptyForm);
            $this->formBuilder->addEventListener(FormEvents::POST_SUBMIT, array(&$this, 'formEventPostSubmit'));
        }
        return $this->formBuilder;
    }

    final public function addField($fieldName, $type, $global = false)
    {
        $this->fields[$fieldName] = array(
            'name' => $fieldName,
            'type' => $type,
            'global' => $global
        );
    }

    final public function getFields()
    {
        if ($this->fields == null) {
            $this->fields = array();
            $this->buildFields();
        }
        return $this->fields;
    }

    abstract protected function buildFields();

    /**
     * @return Form
     */
    final public function getForm()
    {
        if ($this->form == null) {
            $this->buildForm();
            $this->setDefaultHiddenFormFields();
            $this->fb()->setAction($this->generateUrl('_bwcms_admin_preference_save_page', array('type' => $this->getType())));
            $this->fb()->setMethod('POST');
            $this->form = $this->fb()->getForm();
        }
        return $this->form;
    }

    abstract protected function buildForm();


    final public function formEventPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $this->validateForm($event);
    }

    abstract function validateForm(FormEvent $event);

    public function loadCustomField($fieldName, $fieldValue)
    {
        return $fieldValue;
    }

//    abstract public function loadFormData(OptionEntity $option = null, Form $form = null);
//
//    abstract public function prepareEntity(OptionEntity $content = null, $data = array());

    private function setDefaultHiddenFormFields()
    {
        $this->fb()->add('save', 'submit', array(
            'attr' => array('class' => 'save'),
        ));
    }

    /**
     * @return boolean
     */
    public function isPagePreference()
    {
        return $this->isPagePreference;
    }

    /**
     * @param boolean $isPagePreference
     */
    public function setIsPagePreference($isPagePreference)
    {
        $this->isPagePreference = $isPagePreference;
    }

    /**
     * @return EntityManager
     */
    public function em()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return SiteService
     */
    public function sm()
    {
        return $this->container->get('BWCMS.Site')->getManager();
    }

    /**
     * @return ContentService
     */
    public function cm()
    {
        return $this->container->get('BWCMS.Content')->getManager();
    }

    /**
     * @return LocaleService
     */
    public function locale()
    {
        return $this->container->get('BWCMS.Locale')->getManager();
    }

    /**
     * @return MediaService
     */
    public function mm()
    {
        return $this->container->get('BWCMS.Media')->getManager();
    }

    /**
     * @return PreferenceService
     */
    public function pref()
    {
        return $this->container->get('BWCMS.Preference')->getManager();
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

    public function dump($var, $maxDepth = 2, $stripTags = true){
        print '<pre>';
        \Doctrine\Common\Util\Debug::dump($var, $maxDepth, $stripTags);
        print '</pre>';
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return RequestStack
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;
    }
}