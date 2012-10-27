<?php

namespace CodrPress\Model;

use MongoAppKit\Documents\DocumentCollection;

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

    public function findBySlug($year, $month, $day, $slug) {
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = $start + 60 * 60 * 24;

        $conditions = array(
            'created_at' => array(
                '$gt' => new \MongoDate($start),
                '$lt' => new \MongoDate($end)
            ),
            'slugs' => $slug,
            'status' => 'published'
        );

        return $this->find(1, 0, $conditions);
    }

    public function findByTag($tag, $limit = 100, $skip = 0) {
        return $this->find($limit, $skip, array('tags' => $tag, 'status' => 'published'));
    }
}