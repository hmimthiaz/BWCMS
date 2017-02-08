<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Bellwether\BWCMSBundle\Classes\Constants\ContentScopeType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;


use AppKernel;
use Symfony\Component\Validator\Constraints\True;

/**
 * Dashboard controller.
 *
 * @Route("/admin")
 * @Security("has_role('ROLE_BACKEND')")
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

        $returnArray = array();
        $returnArray['user'] = $this->getUser();
        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->getChildrenQueryBuilder(null, false);
        $qb->add('orderBy', 'node.createdDate DESC');
        $registeredContents = $this->cm()->getRegisteredContentTypes();
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }

        $currentSite = $this->sm()->getAdminCurrentSite();
        $qb->andWhere(" node.site = '" . $currentSite->getId() . "' ");
        $qb->andWhere(" node.scope = '" . ContentScopeType::CPublic . "' ");

        $qb->setFirstResult(0);
        $qb->setMaxResults(10);
        $result = $qb->getQuery()->getResult();

        $data = array();
        if (!empty($result)) {
            foreach ($result as $content) {
                $contentClass = $this->cm()->getContentClass($content->getType(), $content->getSchema());

                $ca = array();
                $ca['title'] = $content->getTitle();
                $ca['type'] = $contentClass->getName();

                switch ($content->getStatus()) {
                    case ContentPublishType::Draft:
                        $ca['status'] = 'Draft';
                        break;
                    case ContentPublishType::Published:
                        $ca['status'] = 'Published';
                        break;
                    case ContentPublishType::Expired:
                        $ca['status'] = 'Expired';
                        break;
                    case ContentPublishType::WorkFlow:
                        $ca['status'] = 'WorkFlow';
                        break;
                    default:
                        $ca['status'] = 'Custom';
                }
                $ca['author'] = $content->getAuthor()->getFirstname();
                $ca['createdDate'] = $content->getCreatedDate()->format('Y-m-d H:i:s');
                $ca['edit'] = $this->generateUrl('_bwcms_admin_content_edit', array('contentId' => $content->getId()));
                $ca['link'] = '';
                $contentPublicURL = $contentClass->getPublicURL($content);
                if (!is_null($contentPublicURL)) {
                    $ca['link'] = $contentPublicURL;
                }
                $data[] = $ca;
            }
        }
        $returnArray['data'] = $data;
        return $returnArray;
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
