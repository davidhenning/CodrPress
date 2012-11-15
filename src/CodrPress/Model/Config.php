<?php

namespace CodrPress\Model;

use Silex\Application;

use MongoAppKit\Document\Document;

class Config extends Document
{
    public function __construct(Application $app)
    {
        parent::__construct($app, 'config');
    }
}
