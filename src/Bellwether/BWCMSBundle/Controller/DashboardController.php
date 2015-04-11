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

        $message = \Swift_Message::newInstance()
            ->setSubject('Iris: Welcome Email')
            ->setFrom('imthi@dxb.io')
            ->setTo('hmimthiaz@imthi.com', 'Imthiaz')
            ->setBody(
                $this->renderView(
                    'BWCMSBundle:User:welcome.email.txt.twig',
                    array(
                        'firstname' => 'ffd',
                        'username' => 'dfdf',
                        'password' => 'fdf',
                    )
                )
            );

//        $this->get('mailer')->send($message);

        $this->mailer()->getMailer()->send($message);
        $this->mailer()->getLogger()->dump();


        return array();

    }


}
