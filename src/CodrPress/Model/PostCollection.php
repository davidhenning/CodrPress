<?php

namespace CodrPress\Model;

use MongoAppKit\Document\DocumentCollection;

use Silex\Application;

class PostCollection extends DocumentCollection
{

    public function __construct(Application $app)
    {
        parent::__construct(new Post($app));
    }

    public function findPosts($limit = 10, $offset = 0, $published = true)
    {
        $conditions = array('published_at' => array('$ne' => null));

        if ($published === true) {
            $conditions['status'] = 'published';
        }

        return $this->find($conditions, $limit, $offset);
    }

    public function findPages($limit = 10)
    {
        return $this->find(array('published_at' => null, 'status' => 'published'), $limit);
    }

    public function findBySlug($year, $month, $day, $slug)
    {
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

        return $this->find($conditions, 1);
    }

    public function findByTag($tag, $limit = 100, $offset = 0)
    {
        return $this->find(array('tags' => $tag, 'status' => 'published'), $limit, $offset);
    }
}