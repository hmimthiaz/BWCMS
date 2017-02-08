<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

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



//        $client = new \Google_Client();
//        $client->setAuthConfig('/Users/irafiq/Web/kal.imthi.net/creds.json');
//        $client->addScope(\Google_Service_Analytics::ANALYTICS_READONLY);
//
//        $analytics = new \Google_Service_Analytics($client);
//
//        $profile = $this->getFirstProfileId($analytics);
//        $results = $this->getResults($analytics, $profile);
//        $this->printResults($results);
//
//        dump($profile);
//        exit;

        return $returnArray;
    }

    function getResults($analytics, $profileId) {
        // Calls the Core Reporting API and queries for the number of sessions
        // for the last seven days.
        return $analytics->data_ga->get(
            'ga:' . $profileId,
            '2017-02-01',
            '2017-02-06',
            'ga:sessions');
    }

    function printResults($results) {
        // Parses the response from the Core Reporting API and prints
        // the profile name and total sessions.
        if (count($results->getRows()) > 0) {

            // Get the profile name.
            $profileName = $results->getProfileInfo()->getProfileName();

            // Get the entry for the first entry in the first row.
            $rows = $results->getRows();
            $sessions = $rows[0][0];

            // Print the results.
            print "First view (profile) found: $profileName\n";
            print "Total sessions: $sessions\n";
        } else {
            print "No results found.\n";
        }
    }


    function getFirstProfileId($analytics) {
        // Get the user's first view (profile) ID.

        // Get the list of accounts for the authorized user.
        $accounts = $analytics->management_accounts->listManagementAccounts();

        if (count($accounts->getItems()) > 0) {
            $items = $accounts->getItems();
            $firstAccountId = $items[0]->getId();

            // Get the list of properties for the authorized user.
            $properties = $analytics->management_webproperties
                ->listManagementWebproperties($firstAccountId);

            if (count($properties->getItems()) > 0) {
                $items = $properties->getItems();
                $firstPropertyId = $items[0]->getId();

                // Get the list of views (profiles) for the authorized user.
                $profiles = $analytics->management_profiles
                    ->listManagementProfiles($firstAccountId, $firstPropertyId);

                if (count($profiles->getItems()) > 0) {
                    $items = $profiles->getItems();

                    // Return the first view (profile) ID.
                    return $items[0]->getId();

                } else {
                    throw new Exception('No views (profiles) found for this user.');
                }
            } else {
                throw new Exception('No properties found for this user.');
            }
        } else {
            throw new Exception('No accounts found for this user.');
        }
    }

    /**
     * @Route("/dashboard/auth.php",name="_bwcms_admin_dashboard_auth")
     * @Template()
     */
    public function authAction()
    {

        $client = new \Google_Client();
        $client->setAuthConfig('/Users/irafiq/Web/kal.imthi.net/creds.json');
        $client->addScope(\Google_Service_Analytics::ANALYTICS_READONLY);

        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $client->setRedirectUri($redirect_uri);

        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithRefreshToken($_GET['code']);
            $client->setAccessToken($token);
        }else{
            $url = $client->createAuthUrl();
            return $this->redirect($url);
        }

        dump($client);
        exit;


        return array();
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
