<?php

namespace CodrPress\Model;

use MongoAppKit\Collection\MutableMap,
    MongoAppKit\Document\DocumentCollection;

use Silex\Application;

class ConfigCollection extends DocumentCollection
{
    public function __construct(Application $app)
    {
        parent::__construct(new Config($app));
    }
}
