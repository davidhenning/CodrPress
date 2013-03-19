<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;

use CodrPress\Model\Post,
    CodrPress\Helper\HttpCacheHelper,
    CodrPress\Helper\PaginationHelper;

class HomepageController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];

        $router->get('/', function () use ($app) {
            $content = $app['twig']->render('posts.twig', $this->getTemplateData($app));

            return HttpCacheHelper::getResponse($app, $content, 200);
        })->bind('home');

        $router->get('/{page}/', function ($page) use ($app) {
            $content = $app['twig']->render('posts.twig', $this->getTemplateData($app, $page));

            return HttpCacheHelper::getResponse($app, $content, 200);
        })
            ->bind('home_page')
            ->assert('page', '\d+')
            ->convert('page', function ($page) { return (int)$page; });

        return $router;
    }

    private function getTemplateData(Application $app, $page = 1)
    {
        $posts = Post::posts();
        $pages = Post::pages();
        $tags = Post::tags();

        $config = $app['config'];
        $limit = $config->getProperty('PerPage');
        $offset = $limit * ($page - 1);
        $posts = $posts->limit($limit)->skip($offset);
        $total = $posts->count();
        $templateData = array(
            'config' => $config,
            'posts' => $posts->limit($limit)->skip($offset)->sort(['created_at' => -1]),
            'pages' => $pages->sort(['created_at' => -1]),
            'tags' => $tags
        );

        if ($total > $limit) {
            $pagination = new PaginationHelper($app, 'home_page', array(), $page, $limit);
            $templateData['pagination'] = $pagination->getPagination($total);
        }

        return $templateData;
    }
}