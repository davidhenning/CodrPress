<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Documents\Document;

class PostDocument extends Document {

    public function __construct(Application $app) {
        parent::__construct($app);
        $this->setCollectionName('posts');
    }

    public function getLink() {
        $timestamp = strtotime($this->getProperty('created'));
        $params = array(
            'year' => date('Y', $timestamp),
            'month' => date('m', $timestamp),
            'day' => date('d', $timestamp),
            'slug' => $this->getProperty('slug')
        );

        return $this->_oApp['url_generator']->generate('post', $params);
    }
}
