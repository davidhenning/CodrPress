<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request;

use MongoAppKit\HttpAuthDigest,
    MongoAppKit\Exception\HttpException;

use CodrPress\Model\PostCollection,
    CodrPress\View\PostRestView,
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
        $view = new PostRestView();
        $login = $this->_setUpRestInterface($app);

        $router->get('/posts/', function() use($app, $view) {
            $output = $view->getPostsOutput($app);

            return $app->json($output);
        })->before($login);

        $router->get('/post/{id}/', function($id) use($app, $view) {
            $output = $view->getPostOutput($app, $id);

            return $app->json($output, $output['status']);
        })
        ->assert('id', '[a-z0-9]{24}')
        ->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); })
        ->before($login);

        $router->put('/post/', function() use($app, $view) {
            $output = $view->getPostUpdateOutput($app);

            return $app->json($output, $output['status']);
        })->before($login);

        $router->post('/post/{id}/', function($id) use($app, $view) {
            $output = $view->getPostUpdateOutput($app, $id);

            return $app->json($output, $output['status']);
        })
        ->assert('id', '[a-z0-9]{24}')
        ->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); })
        ->before($login);

        $router->delete('/post/{id}/', function($id) use($app, $view) {
            $output = $view->getPostDeleteOutput($app, $id);

            return $app->json($output, $output['status']);
        })
        ->assert('id', '[a-z0-9]{24}')
        ->convert('id', function($id) use ($app) { return $app['config']->sanitize($id); })
        ->before($login);
    }

    protected function _setUpRestInterface(Application $app) {
        $app->before(function(Request $request) {
            if(strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        $app->error(function(HttpException $e) use($app) {
            if($e->getCode() === 401) {
                return $e->getCallingObject()->sendAuthenticationHeader(true);
            }

            $exceptionHandler = new ExceptionHandler($app['config']);

            return $exceptionHandler->createResponse($e);
        });

        return function(Request $request) use ($app) {
            if(isset($app['unittest']) && $app['unittest'] === true) {
                return null;
            }

            $auth = new HttpAuthDigest($request, 'CodrPress');
            $response = $auth->sendAuthenticationHeader();

            if($response instanceof Response) {
                return $response;
            }

            $auth->authenticate('8514c67a500cb6509b7f240d14761364');

            return null;
        };
    }
}