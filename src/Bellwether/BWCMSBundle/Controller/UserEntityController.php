<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\UserEntity;
use Bellwether\BWCMSBundle\Form\UserEntityType;

/**
 * UserEntity controller.
 *
 * @Route("/userentity")
 */
class UserEntityController extends Controller
{

    /**
     * Lists all UserEntity entities.
     *
     * @Route("/", name="userentity")
     * @Method("GET")
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
     * @Route("/", name="userentity_create")
     * @Method("POST")
     * @Template("BWCMSBundle:UserEntity:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new UserEntity();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('userentity_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a UserEntity entity.
     *
     * @param UserEntity $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(UserEntity $entity)
    {
        $form = $this->createForm(new UserEntityType(), $entity, array(
            'action' => $this->generateUrl('userentity_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new UserEntity entity.
     *
     * @Route("/new", name="userentity_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new UserEntity();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a UserEntity entity.
     *
     * @Route("/{id}", name="userentity_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:UserEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserEntity entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing UserEntity entity.
     *
     * @Route("/{id}/edit", name="userentity_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:UserEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserEntity entity.');
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
    * Creates a form to edit a UserEntity entity.
    *
    * @param UserEntity $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(UserEntity $entity)
    {
        $form = $this->createForm(new UserEntityType(), $entity, array(
            'action' => $this->generateUrl('userentity_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing UserEntity entity.
     *
     * @Route("/{id}", name="userentity_update")
     * @Method("PUT")
     * @Template("BWCMSBundle:UserEntity:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:UserEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find UserEntity entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('userentity_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a UserEntity entity.
     *
     * @Route("/{id}", name="userentity_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BWCMSBundle:UserEntity')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UserEntity entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('userentity'));
    }

    /**
     * Creates a form to delete a UserEntity entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('userentity_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
