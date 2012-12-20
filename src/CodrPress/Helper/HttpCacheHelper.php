<?php

namespace CodrPress\Helper;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse;

class HttpCacheHelper
{

    public static function getResponse(Application $app, $content, $status = 200)
    {
        return self::_prepareResponse($app, new Response($content, $status));
    }

    public static function getJsonResponse(Application $app, $content, $status = 200)
    {
        return self::_prepareResponse($app, new JsonResponse($content, $status));
    }

    protected static function _prepareResponse(Application $app, Response $response)
    {
        $config = $app['config'];

        if ($config->getProperty('UseHttpCache') === true) {
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
