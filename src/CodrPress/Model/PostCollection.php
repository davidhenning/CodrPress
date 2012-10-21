<?php

namespace CodrPress\Model;

use MongoAppKit\Config,
    MongoAppKit\Documents\DocumentCollection;

use Silex\Application;

class PostCollection extends DocumentCollection {

    public function __construct(Application $app) {
        parent::__construct(new Post($app));
    }

    public function findPosts($limit = 10) {
        $cursor = $this->_getDefaultCursor(array('published_at' => array('$ne' => null), 'status' => 'published'));

        return $this->find($limit, 0, $cursor);
    }

    public function findPages($limit = 10) {
        $cursor = $this->_getDefaultCursor(array('published_at' => null, 'status' => 'published'));

        return $this->find($limit, 0, $cursor);
    }

    public function findBySlug($slug, $limit = 100, $skip = 0) {
        $cursor = $this->_getDefaultCursor(array('slugs' => $slug, 'status' => 'published'));

        return $this->find($limit, $skip, $cursor);
    }
}