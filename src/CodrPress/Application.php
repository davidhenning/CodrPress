<?php

namespace CodrPress;

use Silex\Provider\UrlGeneratorServiceProvider;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use MongoAppKit\Application as MongoAppKitApplication,
    MongoAppKit\Config,
    MongoAppKit\Exception\HttpException;

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

        $this->before(function(Request $request) use($config) {
            if(strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        $this->error(function(HttpException $e) use($app) {
            if($e->getCode() === 401) {
                return $e->getCallingObject()->sendAuthenticationHeader(true);
            }

            $exceptionHandler = new ExceptionHandler($app['config']);

            return $exceptionHandler->createResponse($e);
        });

        $this->error(function(\Exception $e) use($app) {
            $request = $app['request'];

            if(strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                $error = array(
                    'status' => 400,
                    'time' => date('Y-m-d H:i:s'),
                    'request' => array(
                        'method' => $request->getMethod(),
                        'url' => $request->getPathInfo()
                    ),
                    'response' => array(
                        'error' => str_ireplace('exception', '', get_class($e)),
                        'message' => $e->getMessage()
                    )
                );

                return $app->json($error, 400);
            }

            $code = ($e->getCode() > 0) ? $e->getCode() : 500;
            $content = $app['twig']->render('error.twig', array(
                'code' => $code,
                'message' => $e->getMessage()
            ));

            return new Response($content, $code);
        });
    }

}
