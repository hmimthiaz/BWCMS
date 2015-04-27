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
use Symfony\Component\Form\FormError;
use Bellwether\Common\StringUtility;
use Doctrine\ORM\QueryBuilder;

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

        if ($request->getMethod() == 'POST') {

            if (strlen($entity->getName()) < 3) {
                $form->get('name')->addError(new FormError('Name too short'));
            }

            if (strlen($entity->getSlug()) < 3) {
                $form->get('slug')->addError(new FormError('Slug too short'));
            } else {
                $criteria = array(
                    'slug' => $entity->getSlug(),
                    'site' => $this->sm()->getAdminCurrentSite()->getId()
                );
                $em = $this->getDoctrine()->getManager();
                $existingEntity = $em->getRepository('BWCMSBundle:ThumbStyle')->findOneBy($criteria);
                if ($existingEntity != null) {
                    $form->get('slug')->addError(new FormError('Slug already exists'));
                }
            }

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $entity->setSite($this->sm()->getAdminCurrentSite());
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('thumbstyle_home'));
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
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
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
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

        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
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
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ThumbStyle entity.
     *
     * @Route("/{id}", name="thumbstyle_update")
     * @Method("POST")
     * @Template("BWCMSBundle:ThumbStyle:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyle')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyle entity.');
        }

        $form = $this->createEditForm($entity);
        $form->handleRequest($request);


        if ($request->getMethod() == 'POST') {

            if (strlen($entity->getName()) < 3) {
                $form->get('name')->addError(new FormError('Name too short'));
            }

            if (strlen($entity->getSlug()) < 3) {
                $form->get('slug')->addError(new FormError('Slug too short'));
            } else {
                $em = $this->getDoctrine()->getManager();
                $qb = $em->createQueryBuilder();
                $queryResult = $qb->select(array('t'))
                    ->from('BWCMSBundle:ThumbStyle', 't')
                    ->andWhere(" t.slug = '" . $entity->getSlug() . "'")
                    ->andWhere(" t.site = '" . $this->sm()->getAdminCurrentSite()->getId() . "'")
                    ->andWhere(" t.id != '" . $entity->getId() . "'")
                    ->getQuery()
                    ->getResult();
                if (!empty($queryResult)) {
                    $form->get('slug')->addError(new FormError('Slug already exists'));
                }
            }

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $entity->setSite($this->sm()->getAdminCurrentSite());
                $em->persist($entity);
                $em->flush();
                return $this->redirect($this->generateUrl('thumbstyle_home'));
            }
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView()
        );
    }
}
