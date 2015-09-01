<?php

namespace Bellwether\BWCMSBundle\Controller;

use Bellwether\BWCMSBundle\Classes\Base\BaseController;
use Bellwether\BWCMSBundle\Classes\Base\BackEndControllerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Page controller.
 *
 * @Route("/admin/editor")
 */
class EditorController extends BaseController implements BackEndControllerInterface
{
    /**
     * @Route("/init.js",name="_bwcms_admin_editor_init")
     * @Template()
     */
    public function initAction(Request $request)
    {
        $templateVariables = array();
        $templateVariables['direction'] = $this->sm()->getAdminCurrentSite()->getDirection();
        $scriptText = $this->renderView('BWCMSBundle:Editor:init.html.twig', $templateVariables);
        $response = new Response($scriptText, 200, array('Content-Type' => 'application/javascript'));
        return $response;
    }

    /**
     * @Route("/browser.php",name="_bwcms_admin_editor_image_browser")
     * @Template()
     */
    public function imageBrowserAction(Request $request)
    {
        $imageURL = $request->get('imageURL', null);
        $selectedContentId = null;
        if (!empty($imageURL)) {
            $imageURL = urldecode($imageURL);
            preg_match('/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $imageURL, $matches);
            if(!empty($matches)){
                $selectedContentId = $matches[0];
            }
        }
        $parentId = 'Root';
        $contentRepo = $this->cm()->getContentRepository();
        $selectContentPath = null;
        if (!is_null($selectedContentId)) {
            $selectedContent = $contentRepo->find($selectedContentId);
            if (!is_null($selectedContent)) {
                $selectContentPath = $contentRepo->getPath($selectedContent);
                if (count($selectContentPath) > 1) {
                    $parentId = $selectContentPath[count($selectContentPath) - 2]->getId();
                }
            }
        }

        $qb = $contentRepo->getChildrenQueryBuilder(null, false);
        $registeredContents = $this->cm()->getRegisteredContentTypes('Media');
        $condition = array();
        foreach ($registeredContents as $cInfo) {
            if ($cInfo['isHierarchy']) {
                $condition[] = " (node.type = '" . $cInfo['type'] . "' AND node.schema = '" . $cInfo['schema'] . "' )";
            }
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere(" node.site ='" . $this->sm()->getAdminCurrentSite()->getId() . "' ");

        $rootFolders = $qb->getQuery()->getResult();

        $jsNodes = array(
            array(
                'id' => 'Root',
                'text' => 'Folders',
                'icon' => 'glyphicon glyphicon-folder-open',
                'parent' => '#',
                'state' => array(
                    'opened' => true,
                    'selected' => ($parentId == 'Root')
                )
            )
        );
        if (!empty($rootFolders)) {
            /** @var ContentEntity $content */
            foreach ($rootFolders as $content) {
                $jsNode = array();
                $jsNode['id'] = $content->getId();
                $jsNode['text'] = $content->getTitle();
                $jsNode['icon'] = 'glyphicon glyphicon-folder-open';
                if ($content->getTreeParent() != null) {
                    $jsNode['parent'] = $content->getTreeParent()->getId();
                } else {
                    $jsNode['parent'] = 'Root';
                }
                if ($parentId == $content->getId()) {
                    $jsNode['state'] = array(
                        'opened' => true,
                        'selected' => true
                    );
                }
                $jsNodes[] = $jsNode;
            }
        }

        return array(
            'parentId' => $parentId,
            'selectContentPath' => $selectContentPath,
            'type' => 'Media',
            'schema' => '',
            'onlyImage' => 'yes',
            'jsNodes' => json_encode($jsNodes),
        );

    }

}
