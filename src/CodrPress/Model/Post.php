<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Document\Document;

class Post extends Document
{

    public function __construct(Application $app)
    {
        parent::__construct($app, 'posts');
    }

    protected function _prepareStore(array $properties) {
        //transform Markdown
        $properties['body_html'] = $this->_app['markdown']->transform($properties['body']);

        return parent::_prepareStore($properties);
    }

    public function getLink()
    {
        $timestamp = $this->getProperty('created_at');
        $params = array(
            'year' => date('Y', $timestamp),
            'month' => date('m', $timestamp),
            'day' => date('d', $timestamp),
            'slug' => $this->getProperty('slugs')->last()
        );

        return $this->_app['url_generator']->generate('post', $params, true);
    }
}
