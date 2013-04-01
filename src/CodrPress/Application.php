<?php

namespace CodrPress;

use Silex\Application as SilexApplication,
    Silex\Provider\TwigServiceProvider,
    Silex\Provider\UrlGeneratorServiceProvider,
    Silex\Provider\WebProfilerServiceProvider;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request;

use SilexMarkdown\Parser\AmplifyrParser;

use SilexMtHaml\MtHamlServiceProvider;

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
        $this['debug'] = $config->get('DebugMode');

        $mango = new Mango($config->get('MongoUri'));
        $dm = new DocumentManager($mango);
        $this['mango.dm'] = $dm;
        ContentHelper::setMarkdown(new AmplifyrParser());

        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $baseDir . "/views",
            'twig.options' => array(
                'cache' => $baseDir . '/cache/twig',
                'auto_reload' => $config->get('DebugMode')
            )
        ));

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new MtHamlServiceProvider());

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
            $content = $app['twig']->render('error.haml', array(
                'code' => $code,
                'message' => $e->getMessage()
            ));

            #echo "{$e->getMessage()}: {$e->getFile()} on line {$e->getLine()}\n";

            return new Response($content, $code);
        });

        $dbConfig = ConfigModel::where(['_id' => 'codrpress'])->first();
        $config->set('DbConfig', $dbConfig);
    }

}