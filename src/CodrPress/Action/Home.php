<?php

namespace CodrPress\Action;

use CodrPress\Action;
use CodrPress\Helper\PaginationHelper;
use CodrPress\Model\Post;

class Home extends Action {

    public function call() {
        $config = $this->getConfig();
        $page = $this->getPage();

        $posts = Post::posts();
        $pages = Post::pages();
        $tags = Post::tags();

        # pagination values
        $limit = $config->get('codrpress.layout.posts_per_page');
        $offset = $limit * ($page - 1);
        $total = $posts->count();

        $dto = [
            'config' => $config,
            'posts' => $posts->limit($limit)->skip($offset)->sort(['created_at' => -1]),
            'pages' => $pages->sort(['created_at' => -1]),
            'tags' => $tags
        ];

        if ($total > $limit) {
            $pagination = new PaginationHelper($app, 'home_page', [], $page, $limit);
            $dto['pagination'] = $pagination->getPagination($total);
        }

        return $dto;
    }

    private function getPage() {
        return $this->getRequest()->get('page', 1);
    }

} 