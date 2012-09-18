<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Documents\Document;

class PostDocument extends Document {

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->setCollectionName('posts');
    }

}
