<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\LocaleEntity;
use Bellwether\BWCMSBundle\Form\LocaleEntityType;

/**
 * LocaleEntity controller.
 *
 * @Route("/admin/locale")
 */
class LocaleController extends Controller
{

    /**
     * Lists all LocaleEntity entities.
     *
     * @Route("/", name="locale_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BWCMSBundle:LocaleEntity')->findAll();

        return array(
            'entities' => $entities,
            'title' => 'Locale',
        );
    }

}
