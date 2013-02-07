<?php

namespace CodrPress;

use Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use MongoAppKit\Application as MongoAppKitApplication,
    MongoAppKit\Config,
    MongoAppKit\Exception\HttpException;

use SilexMarkdown\Parser\AmplifyrParser,
    SilexMarkdown\Provider\MarkdownServiceProvider;

use CodrPress\Model\ConfigCollection;

class Application extends MongoAppKitApplication
{

    /**
     * @param \MongoAppKit\Config $config
     */

    public function __construct(Config $config)
    {
        parent::__construct($config);

        $baseDir = $config->getBaseDir();
        $this['debug'] = $config->getProperty('DebugMode');

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ServiceControllerServiceProvider());
        $this->register(new MarkdownServiceProvider(), array(
            'markdown.parser' => new AmplifyrParser()
        ));

        if ($this['debug'] === true) {
            $profiler = new WebProfilerServiceProvider();
            $this->register($profiler, array(
                'profiler.cache_dir' => $baseDir . '/tmp/profiler',
            ));

            $this->mount('/_profiler', $profiler);
        }

        $app = $this;

        $this->error(function (\Exception $e) use ($app) {
            $request = $app['request'];

            if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
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

            $code = ($e->getCode() > 100 && $e->getCode() <= 600) ? $e->getCode() : 500;
            $content = $app['twig']->render('error.twig', array(
                'code' => $code,
                'message' => $e->getMessage()
            ));

            #echo "{$e->getMessage()}: {$e->getFile()} on line {$e->getLine()}\n";

            return new Response($content, $code);
        });

        $configCollection = new ConfigCollection($app);
        $dbConfig = $configCollection->find(array('_id' => 'codrpress'))->head()->getProperties();
        $config->setProperty('DbConfig', $dbConfig);
    }

}
