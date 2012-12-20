<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use MongoAppKit\HttpAuthDigest,
    MongoAppKit\Exception\HttpException;

use CodrPress\Model\PostCollection,
    CodrPress\ViewHelper\PostRestViewHelper,
    CodrPress\Exception\PostNotFoundException;

class PostController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $pageCollection = new PostCollection($app);
        $pageCollection->sortBy('created_at', 'desc');

        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $intval = function($value) {
            return (int)$value;
        };

        $router->get('/{year}/{month}/{day}/{slug}/', function ($year, $month, $day, $slug) use ($app, $postCollection, $pageCollection) {
            $posts = $postCollection->findBySlug($year, $month, $day, $slug);

            if (count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('post.twig', array(
                'config' => $app['config'],
                'posts' => $posts,
                'pages' => $pageCollection->findPages()
            ));
        })
            ->assert('year', '\d{4}')
            ->assert('month', '\d{1,2}')
            ->assert('day', '\d{1,2}')
            ->convert('year', $intval)
            ->convert('month', $intval)
            ->convert('day', $intval)
            ->convert('slug', $sanitize)
            ->bind('post');

        $this->_connectRestRoutes($app, $router, $sanitize);

        return $router;
    }

    protected function _connectRestRoutes(Application $app, ControllerCollection $router, $sanitize)
    {
        $viewHelper = new PostRestViewHelper();
        $login = $this->_setUpRestInterface($app);
        $validateIdRegex = '[a-z0-9]{24}';

        $router->get('/posts/', function () use ($app, $viewHelper) {
            $output = $viewHelper->getPostsOutput($app);

            return $app->json($output);
        })->before($login);

        $router->get('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $output = $viewHelper->getPostOutput($app, $id);

            return $app->json($output, $output['meta']['status']);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);

        $router->put('/post/', function () use ($app, $viewHelper) {
            $output = $viewHelper->getPostUpdateOutput($app);

            return $app->json($output, $output['meta']['status']);
        })
            ->before($login);

        $router->post('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $output = $viewHelper->getPostUpdateOutput($app, $id);

            return $app->json($output, $output['meta']['status']);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);

        $router->delete('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $output = $viewHelper->getPostDeleteOutput($app, $id);

            return $app->json($output, $output['meta']['status']);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);

        $router->get('/posts/convertMarkdown/', function () use ($app, $viewHelper) {
            $output = $viewHelper->getConvertMarkdownOutput($app);

            return $app->json($output, $output['meta']['status']);
        })
            ->before($login);
    }

    protected function _setUpRestInterface(Application $app)
    {
        $app->before(function (Request $request) {
            if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        $app->error(function (HttpException $e) use ($app) {
            if ($e->getCode() === 401) {
                return $e->getCallingObject()->sendAuthenticationHeader(true);
            }

            $exceptionHandler = new ExceptionHandler($app['config']);

            return $exceptionHandler->createResponse($e);
        });

        return function (Request $request) use ($app) {
            if (isset($app['unittest']) && $app['unittest'] === true) {
                return null;
            }

            $auth = new HttpAuthDigest($request, 'CodrPress');
            $response = $auth->sendAuthenticationHeader();

            if ($response instanceof Response) {
                return $response;
            }

            $auth->authenticate('8514c67a500cb6509b7f240d14761364');

            return null;
        };
    }
}