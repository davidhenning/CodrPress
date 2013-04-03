<?php

namespace CodrPress\Helper;

use Silex\Application;

class PaginationHelper
{

    private $app;
    private $route;
    private $parameters;
    private $limit;
    private $currentPage;
    private $pagination;

    public function __construct(Application $app, $route = '', $parameters = array(), $currentPage = 1, $limit = 10)
    {
        $this->app = $app;
        $this->route = $route;
        $this->parameters = $parameters;
        $this->limit = $limit;
        $this->currentPage = $currentPage;
    }

    public function getPagination($total, $route = null, $parameters = array())
    {
        if ($this->pagination === null) {
            // compute total pages
            $route = (!is_null($route)) ? $route : $this->route;
            $limit = $this->limit;
            $currentPage = $this->currentPage;
            $pageCount = (int)ceil($total / $limit);

            if ($pageCount > 1) {
                // init array of the pagination
                $pages = array(
                    'pages' => array(),
                    'currentPage' => $currentPage,
                    'documentsPerPage' => $limit,
                    'totalPages' => $pageCount
                );

                // set URL to previous page and first page
                if ($currentPage > 1) {
                    $pages['prevPageUrl'] = $this->createPageUrl($route, $parameters, $currentPage - 1);
                    $pages['firstPageUrl'] = $this->createPageUrl($route, $parameters, 1);
                } else {
                    $pages['prevPageUrl'] = false;
                    $pages['firstPageUrl'] = false;
                }

                // set URL to next page and last page
                if ($currentPage < $pageCount) {
                    $pages['nextPageUrl'] = $this->createPageUrl($route, $parameters, $currentPage + 1);
                    $pages['lastPageUrl'] = $this->createPageUrl($route, $parameters, $pageCount);
                } else {
                    $pages['nextPageUrl'] = false;
                    $pages['lastPageUrl'] = false;
                }

                if ($total > $limit) {
                    $pages['pages'] = $this->getPages($route, $parameters, $pageCount);
                }


                $this->pagination = $pages;
            }
        }

        return $this->pagination;
    }

    public function getPages($route, $parameters = array(), $pageCount)
    {
        $pages = array();

        // set pages with number, url and active state
        for ($i = 1; $i <= $pageCount; $i++) {
            $page = array(
                'nr' => $i,
                'url' => $this->createPageUrl($route, $parameters, $i),
                'active' => false
            );

            if ($i === $this->currentPage) {
                $page['active'] = true;
            }

            $pages[] = $page;
        }

        return $pages;
    }

    public function createPageUrl($route, $parameters = array(), $page = 1, $getParameters = array())
    {
        $parameters = array_merge($parameters, array('page' => $page));
        $url = $this->app['url_generator']->generate($route, $parameters);

        if (!empty($getParameters)) {
            $url .= '?' . http_build_query($getParameters);
        }

        return $url;
    }
}
