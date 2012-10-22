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
        return $this->find($limit, 0, array('published_at' => array('$ne' => null), 'status' => 'published'));
    }

    public function findPages($limit = 10) {
        return $this->find($limit, 0, array('published_at' => null, 'status' => 'published'));
    }

    public function findBySlug($slug, $limit = 100, $skip = 0) {
        return $this->find($limit, $skip, array('slugs' => $slug, 'status' => 'published'));
    }
}