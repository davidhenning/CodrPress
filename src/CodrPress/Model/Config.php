<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Document\Document;

class Config extends Document
{
    public function __construct(Application $app)
    {
        parent::__construct($app, 'config');

        $this->setFields(array(
            '_id' => array('mongoType' => 'id', 'index' => true),
            'created_at' => array('mongoType' => 'date', 'index' => true),
            'updated_at' => array('mongoType' => 'date', 'index' => true),
            'published_at' => array('mongoType' => 'date', 'index' => true),
            'blog_title' => array(),
            'author_name' => array()
        ));
    }
}
