<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * Page controller.
 *
 * @Route("/admin/preference")
 */
class PreferenceController extends BaseController
{
    /**
     * @Route("/{type}/index.php",name="preference_page")
     * @Template("BWCMSBundle:Preference:save.html.twig")
     */
    public function loadAction($type)
    {
        $preferenceClass = $this->pref()->getPreferenceClass($type);
        $form = $preferenceClass->getForm();
        $form = $this->pref()->loadFormData($form,$preferenceClass);

        return array(
            'title' => $preferenceClass->getName() .' Preference',
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/{type}/save.php",name="preference_save_page")
     * @Method({"POST"})
     * @Template("BWCMSBundle:Preference:save.html.twig")
     */
    public function saveAction($type)
    {

        $preferenceClass = $this->pref()->getPreferenceClass($type);

        $form = $preferenceClass->getForm();

        return array(
            'title' => $preferenceClass->getName() .' Preference',
            'form' => $form->createView()
        );


    }


}
