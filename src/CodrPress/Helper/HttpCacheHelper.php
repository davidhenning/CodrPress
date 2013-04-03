<?php

namespace CodrPress\Helper;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class HttpCacheHelper
{

    public static function getResponse(Application $app, $content, $status = 200, $useCache = true)
    {
        return self::prepareResponse($app, new Response($content, $status), $useCache);
    }

    public static function getJsonResponse(Application $app, $content, $status = 200, $useCache = true)
    {
        return self::prepareResponse($app, new JsonResponse($content, $status), $useCache);
    }

    private static function prepareResponse(Application $app, Response $response, $useCache = true)
    {
        $config = $app['config'];

        if ($useCache === true && $config->get('codrpress.http.use_cache') === true) {
            $response = self::setCacheHeader($response, $config);
        }

        return $response;
    }

    private static function setCacheHeader(Response $response, $config)
    {
        $expiration = new \DateTime();
        $ttl = $config->get('codrpress.http.cache_ttl');
        $expiration->modify("+ {$ttl} seconds");
        $response->setPublic();
        $response->setMaxAge($ttl);
        $response->setSharedMaxAge($ttl);
        $response->setExpires($expiration);

        return $response;
    }

}
