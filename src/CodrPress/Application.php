<?php

namespace CodrPress;

use Silex\Provider\UrlGeneratorServiceProvider;

use MongoAppKit\Application as MongoAppKitApplication,
    MongoAppKit\Config;

class Application extends MongoAppKitApplication {

    public function __construct(Config $config) {
        parent::__construct($config);

        $this['debug'] = $config->getProperty('DebugMode');

        $this->register(new UrlGeneratorServiceProvider());
    }

}
