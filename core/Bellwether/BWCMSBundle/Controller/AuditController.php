<?php

namespace Bellwether\BWCMSBundle\Controller;


use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\AuditEntity;

use Bellwether\Common\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Audit controller.
 *
 * @Route("/admin/audit")
 */
class AuditController extends BaseController implements BackEndControllerInterface
{

    /**
     * Lists all AuditEntity entities.
     *
     * @Route("/index.php", name="_bwcms_admin_audit_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {

        $query = $request->get('query');

        $pager = new Pagination($request, 50);
        $start = $pager->getStart();
        $limit = $pager->getLimit();

        $auditRepository = $this->em()->getRepository('BWCMSBundle:AuditEntity');
        $qb = $auditRepository->createQueryBuilder('a');
//        $qb->andWhere(" l.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

//        if (!empty($query)) {
//            if ($query != '=') {
//                $searchLikeExp = $qb->expr()->orX();
//                $searchLikeExp->add($qb->expr()->like('l.text', $qb->expr()->literal('%' . $query . '%')));
//                $searchLikeExp->add($qb->expr()->like('l.value', $qb->expr()->literal('%' . $query . '%')));
//                $qb->andWhere($searchLikeExp);
//            } else {
//                $qb->andWhere($qb->expr()->eq('l.text', 'l.value'));
//            }
//        }

        $qb->add('orderBy', 'a.logDate DESC');
        $qb->setFirstResult($start);
        $qb->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        $pager->setItems($result);

        $totalCount = $qb->select('COUNT(a)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $pager->setTotalItems($totalCount);

        return array(
            'pager' => $pager,
            'dir' => $this->sm()->getAdminCurrentSite()->getDirection(),
            'title' => 'Audit',
        );

    }

}
