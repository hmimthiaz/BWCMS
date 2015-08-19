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
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @Route("/resetting/forgot",name="user_forgot")
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
            $formData = $form->getData();
            /**
             * @var \Bellwether\BWCMSBundle\Entity\UserEntity $userEntity
             */
            $userEntity = null;
            if ($form->get('email')->isValid()) {
                $userEntity = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($formData['email']);
                if (null === $userEntity) {
                    $form->get('email')->addError(new FormError('No user associated with that email.'));
                } else if ($userEntity->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
                    $form->get('email')->addError(new FormError('Password reset request already sent.'));
                }

            }
            if ($form->isValid()) {

                if (null === $userEntity->getConfirmationToken()) {
                    $tokenGenerator = $this->container->get('fos_user.util.token_generator');
                    $userEntity->setConfirmationToken($tokenGenerator->generateToken());
                }
                $userEntity->setPasswordRequestedAt(new \DateTime());
                $this->container->get('fos_user.user_manager')->updateUser($userEntity);

                $formSubject = 'Password Request';
                $resetURL = $this->generateUrl('user_forgot_reset', array('token' => $userEntity->getConfirmationToken()), true);
                $emailData = array(
                    'resetURL' => $resetURL
                );
                $emailText = $this->renderView('@Generic/Extras/Forgot.Email.txt.twig', $emailData);
                $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
                if (!is_null($emailSettings['host']) && !empty($emailSettings['host'])) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject($formSubject)
                        ->setFrom($emailSettings['sender_address'])
                        ->addTo($userEntity->getEmail(), $userEntity->getFirstName());
                    $message->setBody($emailText);
                    $this->mailer()->getMailer()->send($message);
                }
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
     * @Route("/resetting/{token}/index.php",name="user_forgot_reset")
     * @Template()
     */
    public function forgotResetAction(Request $request, $token)
    {
        /**
         * @var \Bellwether\BWCMSBundle\Entity\UserEntity $userEntity
         */
        $userEntity = $this->container->get('fos_user.user_manager')->findUserByConfirmationToken($token);

        if (null === $userEntity) {
            throw new NotFoundHttpException('Invalid Code');
        }

        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $newPassword = substr($tokenGenerator->generateToken(), 0, 10);
        $manipulator = $this->container->get('fos_user.util.user_manipulator');
        $manipulator->changePassword($userEntity->getUsername(), $newPassword);

        $userEntity->setConfirmationToken(null);
        $userEntity->setPasswordRequestedAt(null);
        $this->container->get('fos_user.user_manager')->updateUser($userEntity);

        $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
        if (!is_null($emailSettings['host']) && !empty($emailSettings['host'])) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Reset Password Successfully')
                ->setFrom($emailSettings['sender_address'])
                ->setTo($userEntity->getEmail(), $userEntity->getFirstName())
                ->setBody(
                    $this->renderView(
                        'BWCMSBundle:User:reset-password.email.txt.twig',
                        array(
                            'firstName' => $userEntity->getFirstName(),
                            'username' => $userEntity->getEmail(),
                            'loginURL' => $this->generateUrl('user_login', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                            'password' => $newPassword,
                        )
                    )
                );
            $this->mailer()->getMailer()->send($message);
        }
        $template = "@Generic/Extras/Forgot-Success.html.twig";
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
