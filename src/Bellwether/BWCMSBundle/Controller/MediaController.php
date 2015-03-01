<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bellwether\BWCMSBundle\Entity\ContentEntity;

/**
 * Account controller.
 *
 * @Route("/admin/media")
 */
class MediaController extends BaseController
{
    /**
     * @Route("/index",name="media_home")
     * @Template()
     */
    public function indexAction()
    {
        $config = $this->container->getParameter('media.path');
        return array(// ...
        );
    }


    /**
     * @Route("/index/data.php",name="media_home_data")
     * @Method({"GET"})
     */
    public function indexDataAction(Request $request)
    {

        $draw = $request->get('draw', 0);
        $start = $request->get('start', 10);
        $length = $request->get('length', 10);
        $search = $request->get('search');
        if ($search != null && isset($search['value']) && !empty($search['value'])) {
            $searchString = $search['value'];
        }

        $repository = $this->em()->getRepository('BWCMSBundle:ContentEntity');
        /*
         * @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
         */
        $queryBuilder = $repository->createQueryBuilder('c')
            ->select('c.id,c.title,c.name,c.mime,c.extension')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->andWhere(" c.type = 'Media' ")
            ->add('orderBy', 'c.createdDate DESC');

        if (!empty($searchString)) {
            $queryBuilder->andWhere(" c.title LIKE :query1 OR c.name LIKE :query2 ");
            $queryBuilder->setParameter('query1', '%' . $searchString . '%');
            $queryBuilder->setParameter('query2', '%' . $searchString . '%');
        }

        $result = $queryBuilder->getQuery()->getArrayResult();
        $totalCount = $queryBuilder->select('COUNT(c)')->setFirstResult(0)->getQuery()->getSingleScalarResult();
        $data = array();
        $data['draw'] = $draw;
        $data['recordsFiltered'] = $totalCount;
        $data['recordsTotal'] = $totalCount;
        $data['data'] = array();

        if (!empty($result)) {
            foreach ($result as $content) {
                $content['DT_RowId'] = $content['id'];
                $content['thumbnail'] = $this->mm()->getThumbURL($content['name'], $content['mime'], $content['extension'], 64, 64);
                $content['thumbnail'] = '<img src="' . $content['thumbnail'] . '"/>';
                $data['data'][] = $content;
            }
        }
        return $this->returnJsonReponse($request, $data);
    }


    /**
     * @Route("/upload.php",name="media_upload")
     * @Method({"POST"})
     */
    public function uploadAction()
    {

        try {
            $mediaInfo = $this->mm()->handleUpload();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
        if (!empty($mediaInfo)) {
            $content = new ContentEntity();

            $content->setType('Media');
            $content->setSite($this->getSite());

            $content->setTitle($mediaInfo['originalName']);
            $content->setMime($mediaInfo['mimeType']);
            $content->setName($mediaInfo['filename']);
            $content->setSize($mediaInfo['size']);
            $content->setExtension($mediaInfo['extension']);


            $this->cm()->save($content);

        }
        return new Response('Ok', 200);
    }


}
