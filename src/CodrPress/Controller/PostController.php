<?php

namespace CodrPress\Controller;

use CodrPress\Exception\PostNotFoundException;
use CodrPress\Helper\AuthHelper;
use CodrPress\Helper\HttpCacheHelper;
use CodrPress\Helper\PostRestViewHelper;
use CodrPress\Model\Post;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];

        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $intval = function($value) {
            return (int)$value;
        };

        $this->connectRestRoutes($app, $router, $sanitize);

        $router->get('/{year}/{month}/{day}/{slug}/', function ($year, $month, $day, $slug) use ($app) {
            $posts = Post::bySlug($year, $month, $day, $slug);
            $pages = Post::pages();
            $tags = Post::tags();

            if ($posts->count() === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            $content = $app['twig']->render('post.haml', [
                'config' => $app['config'],
                'posts' => $posts,
                'pages' => $pages->sort(['created_at' => -1]),
                'tags' => $tags
            ]);

            return HttpCacheHelper::getResponse($app, $content, 200);
        })
            ->assert('year', '\d{4}')
            ->assert('month', '\d{1,2}')
            ->assert('day', '\d{1,2}')
            ->convert('year', $intval)
            ->convert('month', $intval)
            ->convert('day', $intval)
            ->convert('slug', $sanitize)
            ->bind('post');

        $router->get('/{slug}/', function ($slug) use ($app) {
            $posts = Post::where(['slugs' => $slug, 'published_at' => null, 'status' => 'published']);
            $pages = Post::pages();
            $tags = Post::tags();

            if ($posts->count() === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            $content = $app['twig']->render('post.haml', [
                'config' => $app['config'],
                'posts' => $posts,
                'pages' => $pages->sort(['created_at' => -1]),
                'tags' => $tags
            ]);

            return HttpCacheHelper::getResponse($app, $content, 200);
        })
            ->convert('slug', $sanitize)
            ->bind('page');

        return $router;
    }

    private function connectRestRoutes(Application $app, ControllerCollection $router, $sanitize)
    {
        $viewHelper = new PostRestViewHelper();
        $login = $this->setUpRestInterface($app);
        $validateIdRegex = '[a-z0-9]{24}';

        $router->get('/posts/', function () use ($app, $viewHelper) {
            $content = $viewHelper->getPostsContent($app);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })->before($login);

        $router->get('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $content = $viewHelper->getPostContent($app, $id);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);

        $router->put('/post/', function () use ($app, $viewHelper) {
            $content = $viewHelper->getPostUpdateContent($app);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })
            ->before($login);

        $router->post('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $content = $viewHelper->getPostUpdateContent($app, $id);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);

        $router->delete('/post/{id}/', function ($id) use ($app, $viewHelper) {
            $content = $viewHelper->getPostDeleteContent($app, $id);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->before($login);
    }

    private function setUpRestInterface(Application $app)
    {
        $app->before(function (Request $request) {
            if (strpos($request->headers->get('Content-Type'), 'application/json') === 0) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : []);
            }
        });

        $authHelper = new AuthHelper();
        $authHelper->registerAuthErrorHandler($app);

        return $authHelper->getAuthCallable($app);
    }
}