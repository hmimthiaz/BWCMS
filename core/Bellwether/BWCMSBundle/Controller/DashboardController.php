<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;


use Bellwether\BWCMSBundle\Entity\Site;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
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
        //For now run index here..
        $this->search()->runIndex();

        return array();
    }


    /**
     * @Route("/dashboard/about.php",name="dashboard_about")
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }

}
