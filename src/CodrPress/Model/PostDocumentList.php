<?php

namespace CodrPress\Model;

use MongoAppKit\Config,
    MongoAppKit\Documents\DocumentList;

use Silex\Application;

class PostDocumentList extends DocumentList {

    protected $_sCollectionName = 'posts';

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->setDocumentBaseObject(new PostDocument($app));
    }

    public function findBySlug($slug, $limit = 100, $skip = 0) {
        $cursor = $this->_getDefaultCursor(array('slug' => $slug));

        return $this->find($limit, $skip, $cursor);
    }
}