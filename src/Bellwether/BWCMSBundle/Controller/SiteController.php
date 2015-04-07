<?php

namespace Bellwether\BWCMSBundle\Controller;


use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\SiteEntity;
use Bellwether\BWCMSBundle\Form\SiteEntityType;

/**
 * Site controller.
 *
 * @Route("/admin/site")
 */
class SiteController extends BaseController
{

    /**
     * Lists all SiteEntity entities.
     *
     * @Route("/index.php", name="site_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('BWCMSBundle:SiteEntity')->findAll();
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Lists all SiteEntity entities.
     *
     * @Route("/change.php", name="site_change_current")
     * @Method("GET")
     * @Template()
     */
    public function setCurrentSiteAction(Request $request)
    {
        $siteId = $request->get('siteId');
        if (!is_null($siteId)) {
            $siteEntity = $this->em()->getRepository('BWCMSBundle:SiteEntity')->find($siteId);
            if (!is_null($siteEntity)) {
                $this->sm()->setCurrentSite($siteEntity->getId());
            }
        }
        return $this->redirect($this->generateUrl('dashboard_home'));
    }

    /**
     * Displays a form to create a new SiteEntity entity.
     *
     * @Route("/new.php", name="site_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SiteEntity();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new SiteEntity entity.
     *
     * @Route("/create.php", name="site_create")
     * @Method("POST")
     * @Template("BWCMSBundle:Site:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new SiteEntity();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('site_home'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SiteEntity entity.
     *
     * @param SiteEntity $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SiteEntity $entity)
    {
        $form = $this->createForm(new SiteEntityType(), $entity, array(
            'action' => $this->generateUrl('site_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }


    /**
     * Displays a form to edit an existing SiteEntity entity.
     *
     * @Route("/{id}/edit.php", name="site_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:SiteEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SiteEntity entity.');
        }

        $editForm = $this->createEditForm($entity);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Edits an existing SiteEntity entity.
     *
     * @Route("/{id}/update.php", name="site_update")
     * @Method("POST")
     * @Template("BWCMSBundle:Site:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:SiteEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SiteEntity entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('site_home', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        );
    }

    /**
     * Creates a form to edit a SiteEntity entity.
     *
     * @param SiteEntity $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(SiteEntity $entity)
    {
        $form = $this->createForm(new SiteEntityType(), $entity, array(
            'action' => $this->generateUrl('site_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

}
