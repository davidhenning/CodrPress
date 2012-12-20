<?php

namespace CodrPress\Helper;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse;

class HttpCacheHelper
{

    public static function getResponse(Application $app, $content, $status = 200, $useCache = true)
    {
        return self::_prepareResponse($app, new Response($content, $status), $useCache);
    }

    public static function getJsonResponse(Application $app, $content, $status = 200, $useCache = true)
    {
        return self::_prepareResponse($app, new JsonResponse($content, $status), $useCache);
    }

    protected static function _prepareResponse(Application $app, Response $response, $useCache = true)
    {
        $config = $app['config'];

        if ($useCache === true && $config->getProperty('UseHttpCache') === true) {
            $response = self::_setCacheHeader($response, $config);
        }

        return $response;
    }

    protected static function _setCacheHeader(Response $response, $config)
    {
        $expiration = new \DateTime();
        $ttl = $config->getProperty('HttpCacheTtl');
        $expiration->modify("+ {$ttl} seconds");
        $response->setPublic();
        $response->setMaxAge($ttl);
        $response->setExpires($expiration);

        return $response;
    }

}
