<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Form\User\NewType;
use Bellwether\BWCMSBundle\Form\User\EditType;
use Bellwether\BWCMSBundle\Form\User\ResetPasswordType;
use Bellwether\BWCMSBundle\Form\User\ChangePasswordType;
use Bellwether\BWCMSBundle\Form\User\ProfileType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * User controller.
 *
 * @Route("/admin/user")
 */
class UserController extends BaseController implements BackEndControllerInterface
{


    /**
     * @Route("/",name="user_home")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('BWCMSBundle:UserEntity')->findAll();
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new UserEntity entity.
     *
     * @Route("/create.php", name="user_create")
     * @Template("BWCMSBundle:User:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $roles = $this->acl()->getRoles();
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $password = substr($tokenGenerator->generateToken(), 0, 10);
        $form = $this->createForm(new NewType($roles, $password), null, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Create'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            $userRepo = $this->em()->getRepository('BWCMSBundle:UserEntity');
            if ($form->get('email')->isValid()) {
                $email = $formData['email'];
                $existingUser = $userRepo->findOneBy(array('email' => $email));
                if (!empty($existingUser)) {
                    $form->get('email')->addError(new FormError('Given email already associated with another user.'));
                }
            }

            if ($form->isValid()) {
                /**
                 * @var \FOS\UserBundle\Util\UserManipulator $manipulator
                 */
                $manipulator = $this->container->get('fos_user.util.user_manipulator');
                $username = filter_var($formData['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                /**
                 * @var \Bellwether\BWCMSBundle\Entity\UserEntity $user
                 */
                $user = $manipulator->create($username, $formData['password'], $formData['email'], true, false);
                $user->setFirstname($formData['firstName']);
                $user->setLastname($formData['lastName']);
                $user->setMobile($formData['mobile']);
                foreach ($formData['user_roles'] as $role) {
                    $user->addRole($role);
                }
                $this->em()->persist($user);
                $this->em()->flush();

                $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
                if (!is_null($emailSettings['host']) && !empty($emailSettings['host'])) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Welcome Email')
                        ->setFrom($emailSettings['sender_address'])
                        ->setTo($formData['email'], $formData['firstName'])
                        ->setBody(
                            $this->renderView(
                                'BWCMSBundle:User:welcome.email.txt.twig',
                                array(
                                    'firstName' => $formData['firstName'],
                                    'username' => $formData['email'],
                                    'loginURL' => $this->generateUrl('user_login', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                                    'password' => $password,
                                )
                            )
                        );
                    $this->mailer()->getMailer()->send($message);
                }
                $this->addSuccessFlash('Added new user!');
                return $this->redirect($this->generateUrl('user_home'));
            }
        }

        return array(
            'title' => 'New User',
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new UserEntity entity.
     *
     * @Route("/edit.php", name="user_edit")
     * @Template("BWCMSBundle:User:edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $userId = $request->get('id');
        if (empty($userId)) {
            throw $this->createNotFoundException('Invalid argument');
        }

        $userRepo = $this->em()->getRepository('BWCMSBundle:UserEntity');
        /**
         * @var \Bellwether\BWCMSBundle\Entity\UserEntity $existingUser
         */
        $existingUser = $userRepo->find($userId);
        if (empty($existingUser)) {
            throw $this->createNotFoundException('Unable to find user entity.');
        }
        $roles = $this->acl()->getRoles();
        $form = $this->createForm(new EditType($roles, $existingUser), null, array(
            'action' => $this->generateUrl('user_edit', array('id' => $userId)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Edit'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            if ($form->get('email')->isValid()) {
                $email = $formData['email'];
                $qb = $this->em()->createQueryBuilder();
                $queryResult = $qb->select(array('u'))
                    ->from('BWCMSBundle:UserEntity', 'u')
                    ->andWhere(" u.email = '" . $email . "'")
                    ->andWhere(" u.id != '" . $userId . "'")
                    ->getQuery()
                    ->getResult();
                if (!empty($queryResult)) {
                    $form->get('email')->addError(new FormError('Given email already associated with another user.'));
                }
            }
            if ($form->isValid()) {
                $existingUser->setFirstname($formData['firstName']);
                $existingUser->setLastname($formData['lastName']);
                $existingUser->setEmail($formData['email']);
                $existingUser->setMobile($formData['mobile']);
                foreach ($roles as $roleKey => $roleValue) {
                    $existingUser->removeRole($roleKey);
                }
                foreach ($formData['user_roles'] as $role) {
                    $existingUser->addRole($role);
                }
                $existingUser->setLocked((bool)$formData['locked']);
                $this->em()->persist($existingUser);
                $this->em()->flush();
                $this->addSuccessFlash('Updated user information!');
                return $this->redirect($this->generateUrl('user_home'));
            }
        }

        return array(
            'title' => 'Edit User',
            'form' => $form->createView(),
        );

    }

    /**
     * Creates a new UserEntity entity.
     *
     * @Route("/reset-password.php", name="user_reset_password")
     * @Template("BWCMSBundle:User:edit.html.twig")
     */
    public function resetPasswordAction(Request $request)
    {

        $userId = $request->get('id');
        if (empty($userId)) {
            throw $this->createNotFoundException('Invalid argument');
        }

        $userRepo = $this->em()->getRepository('BWCMSBundle:UserEntity');
        /**
         * @var \Bellwether\BWCMSBundle\Entity\UserEntity $existingUser
         */
        $existingUser = $userRepo->find($userId);
        if (empty($existingUser)) {
            throw $this->createNotFoundException('Unable to find user entity.');
        }

        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $password = substr($tokenGenerator->generateToken(), 0, 10);
        $form = $this->createForm(new ResetPasswordType($existingUser, $password), null, array(
            'action' => $this->generateUrl('user_reset_password', array('id' => $userId)),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Reset Password'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            if ($form->isValid()) {

                $newPassword = $formData['password'];
                $manipulator = $this->container->get('fos_user.util.user_manipulator');
                $manipulator->changePassword($existingUser->getUsername(), $newPassword);

                $emailSettings = $this->pref()->getAllPreferenceByType('Email.SMTP');
                if (!is_null($emailSettings['host']) && !empty($emailSettings['host'])) {
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Reset Password')
                        ->setFrom($emailSettings['sender_address'])
                        ->setTo($formData['email'], $formData['firstName'])
                        ->setBody(
                            $this->renderView(
                                'BWCMSBundle:User:reset-password.email.txt.twig',
                                array(
                                    'firstName' => $existingUser->getFirstName(),
                                    'username' => $existingUser->getEmail(),
                                    'loginURL' => $this->generateUrl('user_login', array(), UrlGeneratorInterface::ABSOLUTE_URL),
                                    'password' => $newPassword,
                                )
                            )
                        );
                    $this->mailer()->getMailer()->send($message);
                }
                $this->addSuccessFlash('Updated user password!');
                return $this->redirect($this->generateUrl('user_home'));
            }
        }

        return array(
            'title' => 'Reset Password',
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new UserEntity entity.
     *
     * @Route("/profile.php", name="user_profile")
     * @Template("BWCMSBundle:User:edit.html.twig")
     */
    public function profileAction(Request $request)
    {
        $userRepo = $this->em()->getRepository('BWCMSBundle:UserEntity');
        /**
         * @var \Bellwether\BWCMSBundle\Entity\UserEntity $existingUser
         */
        $existingUser = $this->getUser();
        if (empty($existingUser)) {
            throw $this->createNotFoundException('Unable to find user entity.');
        }
        $form = $this->createForm(new ProfileType($existingUser), null, array(
            'action' => $this->generateUrl('user_profile'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Save'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            if ($form->isValid()) {
                $existingUser->setFirstname($formData['firstName']);
                $existingUser->setLastname($formData['lastName']);
                $existingUser->setMobile($formData['mobile']);
                $this->em()->persist($existingUser);
                $this->em()->flush();
                $this->addSuccessFlash('Updated profile information!');
                return $this->redirect($this->generateUrl('user_profile'));
            }
        }
        return array(
            'title' => 'My Profile',
            'form' => $form->createView(),
        );
    }


    /**
     * Creates a new UserEntity entity.
     *
     * @Route("/change-password.php", name="user_change_password")
     * @Template("BWCMSBundle:User:edit.html.twig")
     */
    public function changePasswordAction(Request $request)
    {
        /**
         * @var \Bellwether\BWCMSBundle\Entity\UserEntity $existingUser
         */
        $existingUser = $this->getUser();
        if (empty($existingUser)) {
            throw $this->createNotFoundException('Unable to find user entity.');
        }

        $form = $this->createForm(new ChangePasswordType(), null, array(
            'action' => $this->generateUrl('user_change_password'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Update Password'));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            if ($form->get('oldpassword')->isValid()) {
                /**
                 * @var \Symfony\Component\Security\Core\Encoder\EncoderFactory $factory
                 */
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($existingUser);
                if (!$encoder->isPasswordValid($existingUser->getPassword(), $formData['oldpassword'], $existingUser->getSalt())) {
                    $form->get('oldpassword')->addError(new FormError('Old password does not match.'));
                }
            }

            if ($form->isValid()) {
                /**
                 * @var \FOS\UserBundle\Util\UserManipulator $manipulator
                 */
                $manipulator = $this->container->get('fos_user.util.user_manipulator');
                $manipulator->changePassword($existingUser->getUsername(), $formData['password']);
                $this->get('security.token_storage')->setToken(null);
                $this->get('request')->getSession()->invalidate();
                return $this->redirect($this->generateUrl('dashboard_home'));
            }
        }

        return array(
            'title' => 'Update Password',
            'form' => $form->createView(),
        );

    }

}
