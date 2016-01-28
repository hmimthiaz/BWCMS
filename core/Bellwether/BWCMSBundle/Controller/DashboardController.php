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
     * @Route("/", name="_bwcms_admin_redirect_1")
     * @Route("/index.php", name="_bwcms_admin_redirect_2")
     * @Template()
     */
    public function homeRedirectAction()
    {
        return $this->redirectToRoute('_bwcms_admin_dashboard_home');
    }

    /**
     * @Route("/dashboard/index.php",name="_bwcms_admin_dashboard_home")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/dashboard/s3.php",name="_bwcms_admin_s3_home")
     * @Template()
     */
    public function s3Action()
    {

        $nextDate = new \DateTime('Next Sunday');
        dump($nextDate);






//        dump($this->s3Service());
        exit;
        $s3client = $this->s3();

        try {
            $result = $s3client->putObject([
                'Bucket' => 'bwcmstest',
                'Key' => 'hello/bugger/LICENSE.txt',
                'Body' => fopen('/Users/irafiq/Web/kal.imthi.net/LICENSE', 'r'),
                'ACL' => 'public-read',
            ]);
            dump($result);
        } catch (\Aws\Exception\AwsException $e) {
            dump($e);
        }

        exit;
        return array();
    }


    /**
     * @return \Aws\S3\S3Client
     */
    public function s3()
    {
        return $this->container->get('aws.s3');
    }


    /**
     * @Route("/dashboard/email.php",name="_bwcms_admin_dashboard_email")
     * @Template()
     */
    public function emailAction()
    {

        ignore_user_abort(true);
        set_time_limit(0);

        $mailer = $this->mailer();
        $mailer->enableEchoLogger();

        $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
        $adminSettings = $this->pref()->getAllPreferenceByType('General');
        //Admin email
        $message = \Swift_Message::newInstance()
            ->setSubject('Email Test')
            ->setFrom($emailSettings['sender_address'])
            ->addTo($adminSettings['adminEmail']);
        $message->setBody('This is a test email! <br><br>- <strong>Admin</strong>', 'text/html');

        try {
            $mailer->getMailer()->send($message);
        } catch (\Exception $e) {
            //
        }
        exit;
    }

    /**
     * @Route("/dashboard/about.php",name="_bwcms_admin_dashboard_about")
     * @Template()
     */
    public function aboutAction()
    {
        return array();
    }

}
