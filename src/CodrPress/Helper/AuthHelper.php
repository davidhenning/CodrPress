<?php

namespace CodrPress\Helper;

use CodrPress\Exception\HttpException;
use CodrPress\HttpAuthDigest;
use CodrPress\Model\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

class AuthHelper
{
    public function registerAuthErrorHandler(Application $app)
    {
        $app->error(function (Request $request, HttpException $e) use ($app) {
            if ($e->getCode() === 401) {
                $config = $app['config'];
                $auth = new HttpAuthDigest($request, $config->get('codrpress.auth.digest.realm'));

                return $auth->sendAuthenticationHeader(true);
            }

            $exceptionHandler = new ExceptionHandler($app['config']);

            return $exceptionHandler->createResponse($e);
        });
    }

    public function getAuthCallable(Application $app)
    {
        return function (Request $request) use ($app) {
            if (isset($app['unittest']) && $app['unittest'] === true) {
                return null;
            }

            $config = $app['config'];

            $auth = new HttpAuthDigest($request, $config->get('codrpress.auth.digest.realm'));
            $response = $auth->sendAuthenticationHeader();

            if ($response instanceof Response) {
                return $response;
            }

            $username = $config->sanitize($auth->getUserName());
            $user = User::where(['name' => $username])->first();

            if ($user === false) {
                throw new HttpException('Unauthorized', 401);
            }

            $auth->authenticate($user->digest_hash);

            return null;
        };
    }
}