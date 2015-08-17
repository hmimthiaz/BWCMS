<?php

namespace Bellwether\BWCMSBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Security;
use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;


class SecurityController extends BaseController implements BackEndControllerInterface
{

    /**
     * @Route("/login",name="user_login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        $template = sprintf('FOSUserBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        $template = $this->tp()->getCurrentSkin()->getLoginTemplate();

        if (is_null($template)) {
            $template = "@Generic/Extras/Login.html.twig";
        }

        return $this->render($template, array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));

    }

    /**
     * @Route("/forgot",name="user_forgot")
     * @Template()
     */
    public function forgotAction()
    {
        $template = $this->tp()->getCurrentSkin()->getForgotTemplate();
        return $this->render($template, array());
    }

    /**
     * @Route("/login_check",name="user_login_check")
     * @Template()
     */
    public function loginCheckAction()
    {

    }

    /**
     * @Route("/logout",name="user_logout")
     * @Template()
     */
    public function logoutAction()
    {

    }


}
