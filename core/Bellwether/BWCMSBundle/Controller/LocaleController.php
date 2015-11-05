<?php

namespace Bellwether\BWCMSBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bellwether\BWCMSBundle\Entity\LocaleEntity;


use Bellwether\Common\Pagination;
use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Validator\Constraints\True;

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

        $query = $request->get('query');

        $pager = new Pagination($request, 10);
        $start = $pager->getStart();
        $limit = $pager->getLimit();

        $localeRepository = $this->em()->getRepository('BWCMSBundle:LocaleEntity');
        $qb = $localeRepository->createQueryBuilder('l');
        $qb->andWhere(" l.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

        if (!empty($query)) {
            if ($query != '=') {
                $searchLikeExp = $qb->expr()->orX();
                $searchLikeExp->add($qb->expr()->like('l.text', $qb->expr()->literal('%' . $query . '%')));
                $searchLikeExp->add($qb->expr()->like('l.value', $qb->expr()->literal('%' . $query . '%')));
                $qb->andWhere($searchLikeExp);
            } else {
                $qb->andWhere($qb->expr()->eq('l.text', 'l.value'));
            }
        }

        $qb->add('orderBy', 'l.text ASC');
        $qb->setFirstResult($start);
        $qb->setMaxResults($limit);

        $result = $qb->getQuery()->getResult();
        $pager->setItems($result);

        $qb2 = clone $qb; // don't modify existing query
        $qb2->resetDQLPart('orderBy');
        $qb2->resetDQLPart('having');
        $qb2->select('COUNT(l) AS cnt');
        $countResult = $qb2->getQuery()->setFirstResult(0)->getScalarResult();
        $totalCount= $countResult[0]['cnt'];
        $pager->setTotalItems($totalCount);

        return array(
            'pager' => $pager,
            'dir' => $this->sm()->getAdminCurrentSite()->getDirection(),
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

    /**
     * Save a local
     *
     * @Route("/delete.php", name="_bwcms_admin_locale_delete")
     * @Method("POST")
     * @Template()
     */
    public function deleteAction(Request $request)
    {
        $localeId = $request->get('localeId');

        $this->locale()->delete($localeId);
        return $this->returnJsonReponse($request, array());
    }

    /**
     * @param \PHPExcel $objPHPExcel
     * @param $cells
     * @param $color
     */
    function cellColor($objPHPExcel, $cells, $color)
    {
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => $color
            )
        ));
    }

    /**
     * Export to excel.
     *
     * @Route("/export.php", name="_bwcms_admin_locale_export")
     * @Method("GET")
     * @Template()
     */
    public function exportAction(Request $request)
    {
        $localeRepository = $this->em()->getRepository('BWCMSBundle:LocaleEntity');
        $qb = $localeRepository->createQueryBuilder('l');
        $qb->andWhere(" l.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");
        $qb->add('orderBy', 'l.text ASC');
        $qb->setFirstResult(0);
        $qb->setMaxResults(99999);
        $result = $qb->getQuery()->getResult();
        if (empty($result)) {
            throw $this->createNotFoundException('The locale is empty!');
        }

        $phpExcelObject = $this->getExcel()->createPHPExcelObject();

        $documentProperties = $phpExcelObject->getProperties();
        $documentProperties->setCreator("BWCMS");
        $documentProperties->setLastModifiedBy($this->getUser()->getFirstName());
        $documentProperties->setTitle("BWCMS Locale Export Site:" . $this->sm()->getAdminCurrentSite()->getName());

        $sheet = $phpExcelObject->setActiveSheetIndex(0);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setVisible(false);

        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $sheet->setCellValue('A1', 'String Hash');
        $sheet->getComment('A1')->getText()->createTextRun("Don't edit this column values!");

        $sheet->setCellValue('B1', 'Orignal Text');
        $sheet->getComment('B1')->getText()->createTextRun("Don't edit this column values!");

        $sheet->setCellValue('C1', 'New Text');

        $headerStyleArray = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => 'FFFFFF'),
                'size' => 16,
                'name' => 'Verdana'
            ));

        $contentStyleArray = array(
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 14,
                'name' => 'Verdana'
            ));

        $borderStyleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );

        $sheet->getStyle('A1:C1')->applyFromArray($headerStyleArray);
        $this->cellColor($phpExcelObject, 'A1:C1', '000000');

        /**
         * @var LocaleEntity $locale
         */
        $index = 2;
        $totalRows = count($result) + 1;
        foreach ($result as $locale) {
            $sheet->setCellValue('A' . $index, $locale->getHash());
            $sheet->setCellValue('B' . $index, $locale->getText());
            $sheet->setCellValue('C' . $index, $locale->getValue());
            $index++;
        }

        $sheet->getStyle('A2:C' . $totalRows)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A2:C' . $totalRows)->applyFromArray($borderStyleArray);
        if ($this->sm()->getAdminCurrentSite()->getDirection() == 'rtl') {
            $sheet->getStyle('C2:C' . $totalRows)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }
        $sheet->getStyle('C2');

        $sheet->setTitle($this->sm()->getAdminCurrentSite()->getName());
        // create the writer
        $writer = $this->getExcel()->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->getExcel()->createStreamedResponse($writer);
        // adding headers

        $filename = date('YmdHi-') . $this->sm()->getAdminCurrentSite()->getSkinFolderName() . '.xlsx';
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;


    }

    /**
     * @return Factory
     */
    public function getExcel()
    {
        return $this->get('phpexcel');
    }


}
