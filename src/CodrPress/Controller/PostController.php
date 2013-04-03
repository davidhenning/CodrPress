<?php

namespace CodrPress\Controller;

use CodrPress\Exception\HttpException;
use CodrPress\Exception\PostNotFoundException;
use CodrPress\Helper\HttpCacheHelper;
use CodrPress\Helper\PostRestViewHelper;
use CodrPress\HttpAuthDigest;
use CodrPress\Model\Post;
use CodrPress\Model\User;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

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

        $router->get('/{year}/{month}/{day}/{slug}/', function ($year, $month, $day, $slug) use ($app) {
            $posts = Post::bySlug($year, $month, $day, $slug);
            $pages = Post::pages();
            $tags = Post::tags();

            if ($posts->count() === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            $content = $app['twig']->render('post.haml', array(
                'config' => $app['config'],
                'posts' => $posts,
                'pages' => $pages->sort(['created_at' => -1]),
                'tags' => $tags
            ));

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

        $this->connectRestRoutes($app, $router, $sanitize);

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

        $router->get('/posts/convertMarkdown/', function () use ($app, $viewHelper) {
            $content = $viewHelper->getConvertMarkdownContent($app);

            return HttpCacheHelper::getJsonResponse($app, $content, $content['meta']['status'], false);
        })
            ->before($login);
    }

    private function setUpRestInterface(Application $app)
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