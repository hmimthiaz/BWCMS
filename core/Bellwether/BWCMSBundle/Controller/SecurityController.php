<?php

namespace Bellwether\BWCMSBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Security;
use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Bellwether\BWCMSBundle\Form\Security\ForgotType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function forgotAction(Request $request)
    {

        $form = $this->createForm(new ForgotType(), null, array(
            'action' => $this->generateUrl('user_forgot'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Request Password'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();
                $emailId = $data['email'];
                $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($emailId);
                if (null === $user) {
                    $form->get('email')->addError(new FormError('No user associated with that email.'));
                }

                if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
                    $form->get('email')->addError(new FormError('Password reset request already sent.'));
                    return $this->container->get('templating')->renderResponse('FOSUserBundle:Resetting:passwordAlreadyRequested.html.'.$this->getEngine());
                }

                if (null === $user->getConfirmationToken()) {
                    /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
                    $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                    $user->setConfirmationToken($tokenGenerator->generateToken());
                }

                $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
                $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
                $user->setPasswordRequestedAt(new \DateTime());
                $this->container->get('fos_user.user_manager')->updateUser($user);


            }
        }

        $template = $this->tp()->getCurrentSkin()->getForgotTemplate();
        if (is_null($template)) {
            $template = "@Generic/Extras/Forgot.html.twig";
        }

        return $this->render($template, array(
            'form' => $form->createView(),
            'error' => '',
        ));
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
