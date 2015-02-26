<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\Site;
use Bellwether\BWCMSBundle\Entity\ContentEntity;

/**
 * Account controller.
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

        $contentEntity = new ContentEntity();
        $contentEntity->setTitle('hello '. date('dMY') );
        $contentEntity->setSite($siteEntity);
        $contentEntity->setAuthor($this->getUser());
        $this->em()->persist($contentEntity);

        $contentEntity1 = new ContentEntity();
        $contentEntity1->setTitle('hello '. date('dMY') );
        $contentEntity1->setSite($siteEntity);
        $contentEntity1->setAuthor($this->getUser());
        $contentEntity1->setTreeParent($contentEntity);
        $this->em()->persist($contentEntity1);


        $contentEntity2 = new ContentEntity();
        $contentEntity2->setTitle('hello '. date('dMY') );
        $contentEntity2->setSite($siteEntity);
        $contentEntity2->setAuthor($this->getUser());
        $contentEntity2->setTreeParent($contentEntity1);
        $this->em()->persist($contentEntity2);

        $this->em()->flush();




        return new Response('Ok', 200);
    }




}
