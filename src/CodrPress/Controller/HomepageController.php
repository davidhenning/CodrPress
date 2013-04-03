<?php

namespace CodrPress\Controller;

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

        $router->get('/', function () use ($app) {
            $content = $app['twig']->render('posts.haml', $this->getTemplateData($app));

            return HttpCacheHelper::getResponse($app, $content, 200);
        })->bind('home');

        $router->get('/{page}/', function ($page) use ($app) {
            $content = $app['twig']->render('posts.haml', $this->getTemplateData($app, $page));

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
        $limit = $config->get('codrpress.layout.posts_per_page');
        $offset = $limit * ($page - 1);
        $posts = $posts->limit($limit)->skip($offset);
        $total = $posts->count();
        $templateData = array(
            'config' => $config,
            'posts' => $posts->limit($limit)->skip($offset)->sort(['created_at' => -1]),
            'pages' => $pages->sort(['created_at' => -1]),
            'tags' => $tags
        );

        #print_r($templateData['posts']->all());

        if ($total > $limit) {
            $pagination = new PaginationHelper($app, 'home_page', array(), $page, $limit);
            $templateData['pagination'] = $pagination->getPagination($total);
        }

        return $templateData;
    }
}