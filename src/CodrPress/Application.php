<?php

namespace CodrPress;

use Silex\Provider\UrlGeneratorServiceProvider;

use MongoAppKit\Application as MongoAppKitApplication,
    MongoAppKit\Config;

use SilexMarkdown\Provider\MarkdownServiceProvider;

class Application extends MongoAppKitApplication {

    /**
     * @param \MongoAppKit\Config $config
     */

    public function __construct(Config $config) {
        parent::__construct($config);

        $this['debug'] = $config->getProperty('DebugMode');

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new MarkdownServiceProvider());
    }

}
