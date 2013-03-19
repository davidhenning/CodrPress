<?php

namespace CodrPress;

use Silex\Application as SilexApplication,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\ServiceControllerServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request;

use SilexMarkdown\Parser\AmplifyrParser,
    SilexMarkdown\Provider\MarkdownServiceProvider;

use Mango\Mango,
    Mango\DocumentManager;

use CodrPress\Model\Config as ConfigModel,
    CodrPress\Helper\ContentHelper;

class Application extends SilexApplication
{

    /**
     * @param \CodrPress\Config $config
     */

    public function __construct(Config $config)
    {
        parent::__construct();
        $this['config'] = $config;

        $baseDir = $config->getBaseDir();
        $this['debug'] = $config->getProperty('DebugMode');

        $mango = new Mango($config->getProperty('MongoUri'));
        $dm = new DocumentManager($mango);
        $this['mango.dm'] = $dm;

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $baseDir . "/views",
            'twig.options' => array(
                'cache' => $baseDir . '/tmp/twig',
                'auto_reload' => $config->getProperty('DebugMode')
            )
        ));

        $this->register(new UrlGeneratorServiceProvider());
        #$this->register(new ServiceControllerServiceProvider());
        $this->register(new MarkdownServiceProvider(), array(
            'markdown.parser' => new AmplifyrParser()
        ));

        ContentHelper::setMarkdown($this['markdown']);

        /*
        if ($this['debug'] === true) {
            $profiler = new WebProfilerServiceProvider();
            $this->register($profiler, array(
                'profiler.cache_dir' => $baseDir . '/tmp/profiler',
            ));

            $this->mount('/_profiler', $profiler);
        }
        */
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

        $dbConfig = ConfigModel::where(['_id' => 'codrpress'])->head()->getProperties();
        $config->setProperty('DbConfig', $dbConfig);
    }

}