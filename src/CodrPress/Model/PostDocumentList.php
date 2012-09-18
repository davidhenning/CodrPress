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

}