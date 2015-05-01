<?php

namespace Bellwether\Common;

use Symfony\Component\HttpFoundation\Request;


class Pagination
{


    private $pageVar;

    private $currentPage;

    private $totalPages;

    private $start;

    private $limit;

    private $totalItems;

    private $items;

    private $activeClass = 'active';

    /**
     * @var Request
     */
    private $request;


    function __construct(Request $request, $limit = 5, $pageVar = 'page')
    {
        $this->setRequest($request);
        $this->setLimit($limit);
        $this->setPageVar($pageVar);
        $this->init();
    }

    public function init()
    {
        $this->totalItems = 0;
        $this->setItems(array());
        $this->setCurrentPage($this->getRequest()->get($this->getPageVar(), 1));
        $this->setStart(($this->getCurrentPage() - 1) * $this->getLimit());
    }

    public function prev()
    {
        if ($this->getCurrentPage() <= 1) {
            return null;
        }
        return $this->getPageInfo($this->getCurrentPage() - 1, 'Previous');
    }

    public function pages()
    {
        $pages = array();
        for ($page = 1; $page <= $this->getTotalPages(); $page++) {
            $pages[] = $this->getPageInfo($page);
        }
        return $pages;
    }

    public function next()
    {
        if ($this->getCurrentPage() >= $this->getTotalPages()) {
            return null;
        }
        return $this->getPageInfo($this->getCurrentPage() + 1, 'Next');
    }

    private function calculatePages()
    {
        if ($this->totalItems > 0 && $this->limit > 0) {
            if (floor($this->totalItems / $this->limit) < ($this->totalItems / $this->limit)) {
                $this->totalPages = floor($this->totalItems / $this->limit) + 1;
            } else {
                $this->totalPages = floor($this->totalItems / $this->limit);
            }
        } else {
            $this->totalPages = 1;
        }
    }

    private function getPageInfo($page, $text = null)
    {
        if (empty($text)) {
            $text = $page;
        }
        $class = null;
        if ($page == $this->getCurrentPage()) {
            $class = $this->getActiveClass();
        }
        return array(
            'text' => $text,
            'class' => $class,
            'params' => $this->getURLParamsWithPage($page),
            'route' => $this->request->get('_route')
        );
    }

    private function getURLParamsWithPage($page)
    {
        $routeParams = $this->request->get('_route_params');
        $queryParams = $this->request->query->all();
        $allParams = array_merge($routeParams, $queryParams);
        if ($page != 1) {
            $allParams[$this->pageVar] = $page;
        } else {
            $allParams[$this->pageVar] = null;
        }
        return $allParams;
    }

    /**
     * @return string
     */
    public function getPageVar()
    {
        return $this->pageVar;
    }

    /**
     * @param string $pageVar
     */
    public function setPageVar($pageVar)
    {
        $this->pageVar = $pageVar;
    }

    /**
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param mixed $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        $this->calculatePages();
    }

    /**
     * @return Array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Array $items
     */
    public function setItems($items)
    {
        if (empty($items)) {
            $items = array();
        }
        $this->items = $items;
    }

    /**
     * @return mixed
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * @param mixed $totalPages
     */
    public function setTotalPages($totalPages)
    {
        $this->totalPages = $totalPages;
    }

    /**
     * @return string
     */
    public function getActiveClass()
    {
        return $this->activeClass;
    }

    /**
     * @param string $activeClass
     */
    public function setActiveClass($activeClass)
    {
        $this->activeClass = $activeClass;
    }

}
