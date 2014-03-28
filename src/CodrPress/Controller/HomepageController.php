<?php

namespace CodrPress\Controller;

use CodrPress\Action\Home;
use CodrPress\Helper\HttpCacheHelper;
use CodrPress\Helper\PaginationHelper;
use CodrPress\Model\Post;
use Silex\Application;
use Silex\ControllerProviderInterface;

class HomepageController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];

        $home = function () use ($app) {
            $action = new Home($app);
            $response = $app['twig']->render('posts.haml', $action->getResponse());

            return HttpCacheHelper::getResponse($app, $response, 200);
        };

        $router->get('/', $home)->bind('home');

        $router
            ->get('/{page}/', $home)
            ->bind('home_page')
            ->assert('page', '\d+')
            ->convert('page', function ($page) { return (int)$page; });

        return $router;
    }

}