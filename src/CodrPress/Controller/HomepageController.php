<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;

use CodrPress\Model\PostCollection;

class HomepageController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $router = $app['controllers_factory'];
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $pageCollection = new PostCollection($app);
        $pageCollection->sortBy('created_at', 'desc');

        $router->get('/', function() use($app, $postCollection, $pageCollection) {
            return $app['twig']->render('posts.twig', array(
                'posts' => $postCollection->findPosts(),
                'pages' => $pageCollection->findPages()
            ));
        })->bind('home');

        return $router;
    }
}