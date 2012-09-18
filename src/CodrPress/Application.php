<?php

namespace CodrPress;

use MongoAppKit\Application as MongoAppKitApplication,
    MongoAppKit\Config;

class Application extends MongoAppKitApplication {

    public function __construct(Config $config) {
        parent::__construct($config);

        $this['debug'] = $config->getProperty('DebugMode');
    }

}
