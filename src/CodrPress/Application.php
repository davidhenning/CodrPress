<?php

namespace CodrPress;

use Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\HttpFoundation\Response;

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

        $app = $this;

        $this->error(function(\Exception $e) use($app) {
            $code = ($e->getCode() > 0) ? $e->getCode() : 500;
            $content = $app['twig']->render('error.twig', array(
                'code' => $code,
                'message' => $e->getMessage()
            ));

            return new Response($content, $code);
        });
    }

}
