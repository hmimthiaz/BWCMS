<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BWCMSBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\Site;
use Bellwether\BWCMSBundle\Entity\Content;

/**
 * Account controller.
 *
 * @Route("/admin/dashboard")
 */
class DashboardController extends BWCMSBaseController
{
    /**
     * @Route("/index",name="dashboard_home")
     * @Template()
     */
    public function indexAction()
    {
        $x=10;

        //throw new Exception('Division by zero.');

        return array(// ...
        );
    }


    /**
     * @Route("/setup",name="dashboard_setup")
     * @Template()
     */
    public function setupAction()
    {
        $siteEntity = new Site();
        $siteEntity->setName('Main');

        $this->em()->persist($siteEntity);
        $this->em()->flush();



        return new Response('Ok', 200);
    }




}
