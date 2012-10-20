<?php

namespace CodrPress\Model;

use MongoAppKit\Config,
    MongoAppKit\Documents\DocumentList;

use Silex\Application;

class PostDocumentList extends DocumentList {

    protected $_collectionName = 'posts';

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->setDocumentBaseObject(new PostDocument($app));
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