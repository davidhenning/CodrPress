<?php

namespace CodrPress\Helper;

use Silex\Application;

class Pagination {

    protected $_app;
    protected $_route;
    protected $_limit;
    protected $_currentPage;
    protected $_pagination;

    public function __construct(Application $app, $route = '', $limit = 10, $currentPage = 1) {
        $this->_app = $app;
        $this->_route = $route;
        $this->_limit = $limit;
        $this->_currentPage = $currentPage;
    }

    public function getPagination($total, $route = null) {
        if($this->_pagination === null) {
            // compute total pages
            $route = (!is_null($route)) ? $route : $this->_route;
            $limit = $this->_limit;
            $currentPage = $this->_currentPage;
            $pageCount = (int)ceil($total / $limit);

            if($pageCount > 1) {
                // init array of the pagination
                $pages = array(
                    'pages' => array(),
                    'currentPage' => $currentPage,
                    'documentsPerPage' => $limit,
                    'totalPages' => $pageCount
                );

                // set URL to previous page and first page
                if($currentPage > 1) {
                    $pages['prevPageUrl'] = $this->createPageUrl($route, $currentPage - 1);
                    $pages['firstPageUrl'] = $this->createPageUrl($route, 1);
                } else {
                    $pages['prevPageUrl'] = false;
                    $pages['firstPageUrl'] = false;
                }

                // set URL to next page and last page
                if($currentPage < $pageCount) {
                    $pages['nextPageUrl'] = $this->createPageUrl($route, $currentPage + 1);
                    $pages['lastPageUrl'] = $this->createPageUrl($route, $pageCount);
                } else {
                    $pages['nextPageUrl'] = false;
                    $pages['lastPageUrl'] = false;
                }
                if($total > $limit) {
                    $pages['pages'] = $this->getPages($route, $pageCount);
                }


                $this->_pagination = $pages;
            }
        }

        return $this->_pagination;
    }

    public function getPages($route, $pageCount) {
        $pages = array();

        // set pages with number, url and active state
        for($i = 1; $i <= $pageCount; $i++) {
            $page = array(
                'nr' => $i,
                'url' => $this->createPageUrl($route, $i),
                'active' => false
            );

            if($i === $this->_currentPage) {
                $page['active'] = true;
            }

            $pages[] = $page;
        }

        return $pages;
    }

    public function createPageUrl($route, $page, $params = array()) {
        $url = $this->_app['url_generator']->generate($route, array('page' => $page));

        if(!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
