<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;
use Bellwether\BWCMSBundle\Form\ThumbStyleType;
use Symfony\Component\Form\FormError;
use Bellwether\Common\StringUtility;
use Doctrine\ORM\QueryBuilder;

/**
 * Site controller.
 *
 * @Route("/admin/thumbstyle")
 */
class ThumbStyleController extends BaseController  implements BackEndControllerInterface
{

    /**
     * Lists all ThumbStyleEntity entities.
     *
     * @Route("/", name="_bwcms_admin_thumbstyle_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BWCMSBundle:ThumbStyleEntity')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new ThumbStyleEntity entity.
     *
     * @Route("/", name="_bwcms_admin_thumbstyle_create")
     * @Method("POST")
     * @Template("BWCMSBundle:ThumbStyleEntity:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ThumbStyleEntity();
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
                $existingEntity = $em->getRepository('BWCMSBundle:ThumbStyleEntity')->findOneBy($criteria);
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
     * Creates a form to create a ThumbStyleEntity entity.
     *
     * @param ThumbStyleEntity $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ThumbStyleEntity $entity)
    {
        $form = $this->createForm(new ThumbStyleType(), $entity, array(
            'action' => $this->generateUrl('thumbstyle_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ThumbStyleEntity entity.
     *
     * @Route("/new", name="_bwcms_admin_thumbstyle_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ThumbStyleEntity();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ThumbStyleEntity entity.
     *
     * @Route("/{id}/edit", name="_bwcms_admin_thumbstyle_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyleEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyleEntity entity.');
        }

        $editForm = $this->createEditForm($entity);

        return array(
            'entity' => $entity,
            'form' => $editForm->createView()
        );
    }

    /**
     * Creates a form to edit a ThumbStyleEntity entity.
     *
     * @param ThumbStyleEntity $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(ThumbStyleEntity $entity)
    {
        $form = $this->createForm(new ThumbStyleType(), $entity, array(
            'action' => $this->generateUrl('thumbstyle_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ThumbStyleEntity entity.
     *
     * @Route("/{id}", name="_bwcms_admin_thumbstyle_update")
     * @Method("POST")
     * @Template("BWCMSBundle:ThumbStyleEntity:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWCMSBundle:ThumbStyleEntity')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ThumbStyleEntity entity.');
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
                    ->from('BWCMSBundle:ThumbStyleEntity', 't')
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
