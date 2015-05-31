<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
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
 * @Route("/admin")
 */
class DashboardController extends BaseController implements BackEndControllerInterface
{

    /**
     * @Route("/")
     * @Route("/index.php")
     * @Template()
     */
    public function homeRedirectAction()
    {
        return $this->redirectToRoute('dashboard_home');
    }

    /**
     * @Route("/dashboard/index.php",name="dashboard_home")
     * @Template()
     */
    public function indexAction()
    {
//        $this->addSuccessFlash('Success Message');
//        $this->addInfoFlash('Information Message');
//        $this->addWarningFlash('Warning Message');
//        $this->addDangerFlash('Danger');
        return array();
    }


}
