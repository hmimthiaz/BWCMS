<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\Site;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Symfony\Component\Form\Form;
use AppKernel;

/**
 * Dashboard controller.
 *
 * @Route("/admin/dashboard")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/index",name="dashboard_home")
     * @Template()
     */
    public function indexAction()
    {

        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $values = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $this->dump($values);
        return array(
        );

    }





}
