<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\LocaleEntity;

use Bellwether\Common\Pagination;

/**
 * LocaleEntity controller.
 *
 * @Route("/admin/locale")
 */
class LocaleController extends BaseController implements BackEndControllerInterface
{

    /**
     * Lists all LocaleEntity entities.
     *
     * @Route("/index.php", name="_bwcms_admin_locale_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $pager = new Pagination($request, 10);

        $start = $pager->getStart();
        $limit = $pager->getLimit();

        $localeRepository = $this->em()->getRepository('BWCMSBundle:LocaleEntity');
        $qb = $localeRepository->createQueryBuilder('l');
        $qb->andWhere(" l.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");
        $qb->add('orderBy', 'l.text ASC');
        $qb->setFirstResult($start);
        $qb->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        $pager->setItems($result);

        $totalCount = $qb->select('COUNT(l)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $pager->setTotalItems($totalCount);

        return array(
            'pager' => $pager,
            'title' => 'Locale',
        );
    }


    /**
     * Save a local
     *
     * @Route("/save.php", name="_bwcms_admin_locale_save")
     * @Method("POST")
     * @Template()
     */
    public function saveAction(Request $request)
    {
        $localeId = $request->get('localeId');
        $newValue = $request->get('newValue');

        $this->locale()->save($localeId, $newValue);
        return $this->returnJsonReponse($request, array());
    }


}
