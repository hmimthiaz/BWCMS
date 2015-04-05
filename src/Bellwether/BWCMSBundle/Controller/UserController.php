<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Form\NewUserEntityType;


/**
 * User controller.
 *
 * @Route("/admin/user")
 */
class UserController extends BaseController
{
    /**
     * @Route("/",name="user_home")
     * @Template()
     */
    public function indexAction()
    {

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
     * @Template("BWCMSBundle:User:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new UserEntity();

        $form = $this->createForm(new NewUserEntityType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Create'));

        $form->handleRequest($request);
        if ($form->isValid()) {

            if (!$this->container->has('fos_user.util.user_manipulator')) {
                throw new \LogicException('The DoctrineBundle is not registered in your application.');
            }

            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $password = substr($tokenGenerator->generateToken(), 0, 12);

            $manipulator = $this->container->get('fos_user.util.user_manipulator');
            $username = filter_var($entity->getEmail(), FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            /**
             * @var \Bellwether\BWCMSBundle\Entity\UserEntity $user
             */
            $user = $manipulator->create($username, $password, $entity->getEmail(), true, false);
            $user->setFirstname($entity->getFirstname());
            $user->setLastname($entity->getLastname());
            $user->setMobile($entity->getMobile());

            $this->em()->persist($user);
            $this->em()->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Iris: Welcome Email')
                ->setFrom('iris@dxb.io')
                ->setTo($entity->getEmail(),$entity->getFirstname())
                ->setBody(
                    $this->renderView(
                        'IrisAdminBundle:User:welcome.email.txt.twig',
                        array(
                            'firstname' => $entity->getFirstname(),
                            'username' => $entity->getEmail(),
                            'password' => $password,
                        )
                    )
                );
            $this->get('mailer')->send($message);

            return $this->redirect($this->generateUrl('user_home', array('id' => $user->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }


}
