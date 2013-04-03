<?php

namespace CodrPress;

use CodrPress\Helper\ContentHelper;
use Mango\DocumentManager;
use Mango\Mango;
use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use SilexMarkdown\Parser\AmplifyrParser;
use SilexMtHaml\MtHamlServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application extends SilexApplication
{

    /**
     * @param \CodrPress\Config $config
     */

    public function __construct(Config $config)
    {
        parent::__construct();

        $this['config'] = $config;
        $this['debug'] = $config->get('codrpress.debug');

        $mango = new Mango($config->get('codrpress.db.mongo.uri'));
        $dm = new DocumentManager($mango);
        $this['mango.dm'] = $dm;

        ContentHelper::setMarkdown(new AmplifyrParser());

        $baseDir = $config->getBaseDir();
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $baseDir . "/views",
            'twig.options' => array(
                'cache' => $baseDir . '/cache/twig',
                'auto_reload' => $config->get('codrpress.debug')
            )
        ));

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new MtHamlServiceProvider());

        $this->handleErrors();
    }

    private function handleErrors()
    {
        $this->error(function (\Exception $e) {
            $request = $this['request'];

            if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                return $this->getJsonErrorReponse($request, $e);
            }

            return $this->getErrorReponse($request, $e);
        });
    }

    private function getErrorHttpStatus($code)
    {
        return ($code > 100 && $code <= 600) ? $code : 500;
    }

    private function getErrorReponse(Request $request, \Exception $e)
    {
        $code = $this->getErrorHttpStatus($e->getCode());
        $content = $this['twig']->render('error.haml', array(
            'code' => $code,
            'message' => $e->getMessage()
        ));

        return new Response($content, $code);
    }

    private function getJsonErrorReponse(Request $request, \Exception $e)
    {
        $code = $this->getErrorHttpStatus($e->getCode());
        $error = array(
            'status' => $code,
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

        return $this->json($error, $code);
    }
}