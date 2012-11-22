<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Collection\MutableMap,
    MongoAppKit\Document\Document;

class Post extends Document
{

    public function __construct(Application $app)
    {
        parent::__construct($app, 'posts');

        $this->setFields(array(
            '_id' => array('mongoType' => 'id', 'index' => true),
            'created_at' => array('mongoType' => 'date', 'index' => true),
            'updated_at' => array('mongoType' => 'date', 'index' => true),
            'published_at' => array('mongoType' => 'date', 'index' => true),
            'title' => array(),
            'subtitle' => array(),
            'body' => array(),
            'body_html' => array(),
            'slugs' => array('index' => true),
            'status' => array('index' => true),
            'disqus' => array(),
            'tags' => array('index' => true)
        ));
    }

    protected function _prepareStore(array $properties)
    {
        //transform Markdown
        $properties['body_html'] = $this->_app['markdown']->transform($properties['body']);

        // create slugs
        $properties['slugs'] = $this->_createSlugs($properties['slugs'], $properties['title']);

        return parent::_prepareStore($properties);
    }

    protected function _createSlugs($slugs, $title)
    {
        if (!is_array($slugs)) {
            $slugs = array($slugs);
        }

        $slugs = array_merge($slugs, array($title));

        $slugs = new MutableMap($slugs);
        $slugs->map(function($value) {
            $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            $slug = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $slug);
            $slug = strtolower(trim($slug, '-'));
            $slug = preg_replace("/[\/_| -]+/", '-', $slug);

            return $slug;
        })->unique();

        return $slugs->getArray();
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
