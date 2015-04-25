<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\ThumbStyle;
use Bellwether\BWCMSBundle\Form\ThumbStyleType;

/**
 * Site controller.
 *
 * @Route("/admin/thumbstyle")
 */
class ThumbStyleController extends BaseController
{

    /**
     * Lists all ThumbStyle entities.
     *
     * @Route("/", name="thumbstyle_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BWCMSBundle:ThumbStyle')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new ThumbStyle entity.
     *
     * @Route("/", name="thumbstyle_create")
     * @Method("POST")
     * @Template("BWCMSBundle:ThumbStyle:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ThumbStyle();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('thumbstyle_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a ThumbStyle entity.
     *
     * @param ThumbStyle $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ThumbStyle $entity)
    {
        $form = $this->createForm(new ThumbStyleType(), $entity, array(
            'action' => $this->generateUrl('thumbstyle_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ThumbStyle entity.
     *
     * @Route("/new", name="thumbstyle_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ThumbStyle();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ThumbStyle entity.
     *
     * @Route("/{id}", name="thumbstyle_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyle entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ThumbStyle entity.
     *
     * @Route("/{id}/edit", name="thumbstyle_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyle entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a ThumbStyle entity.
    *
    * @param ThumbStyle $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ThumbStyle $entity)
    {
        $form = $this->createForm(new ThumbStyleType(), $entity, array(
            'action' => $this->generateUrl('thumbstyle_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ThumbStyle entity.
     *
     * @Route("/{id}", name="thumbstyle_update")
     * @Method("PUT")
     * @Template("BWCMSBundle:ThumbStyle:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyle entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('thumbstyle_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ThumbStyle entity.
     *
     * @Route("/{id}", name="thumbstyle_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BWCMSBundle:ThumbStyle')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ThumbStyle entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('thumbstyle'));
    }

    /**
     * Creates a form to delete a ThumbStyle entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('thumbstyle_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
