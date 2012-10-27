<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use CodrPress\Model\PostCollection,
    CodrPress\Exception\PostNotFoundException;

class PostController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $router = $app['controllers_factory'];
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $pageCollection = new PostCollection($app);
        $pageCollection->sortBy('created_at', 'desc');

        $router->get('/{year}/{month}/{day}/{slug}/', function($year, $month, $day, $slug) use($app, $postCollection, $pageCollection) {
            $posts = $postCollection->findBySlug($year, $month, $day, $slug);

            if(count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('post.twig', array(
                'posts' => $posts,
                'pages' => $pageCollection->findPages()
            ));
        })
        ->assert('year', '\d{4}')
        ->assert('month', '\d{1,2}')
        ->assert('day', '\d{1,2}')
        ->convert('year', function($year) { return (int)$year; })
        ->convert('month', function($month) { return (int)$month; })
        ->convert('day', function($day) { return (int)$day; })
        ->convert('slug', function($slug) use ($app) { return $app['config']->sanitize($slug); })
        ->bind('post');

        $this->_connectRestRoutes($app, $router);

        return $router;
    }

    protected function _connectRestRoutes(Application $app, ControllerCollection $router) {
        $router->get('/post/{id}/', function($id) use($app) {
            return $app->json(array('code' => 200, 'id' => $id));
        })->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); });

        $router->put('/post/', function() use($app) {
            $request = $app['request'];
            $payload = $request->request->get('payload');

            return $app->json(array('code' => 200, 'payload' => $payload));
        });

        $router->post('/post/{id}/', function($id) use($app) {
            $request = $app['request'];
            $payload = $request->request->get('payload');

            return $app->json(array('code' => 200, 'id' => $id, 'payload' => $payload));
        })->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); });

        $router->delete('/post/{id}/', function($id) use($app) {
            return $app->json(array('code' => 200, 'id' => $id));
        })->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); });
    }
}