<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;


use CodrPress\Model\PostCollection,
    CodrPress\Helper\HttpCacheHelper,
    CodrPress\Helper\Pagination;

class HomepageController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $self = $this;

        $router->get('/', function () use ($app, $self) {
            $content = $app['twig']->render('posts.twig', $self->getTemplateData($app));

            return HttpCacheHelper::getResponse($app, $content, 200);
        })->bind('home');

        $router->get('/{page}/', function ($page) use ($app, $self) {
            $content = $app['twig']->render('posts.twig', $self->getTemplateData($app, $page));

            return HttpCacheHelper::getResponse($app, $content, 200);
        })
            ->bind('home_page')
            ->assert('page', '\d+')
            ->convert('page', function ($page) { return (int)$page; });

        return $router;
    }

    public function getTemplateData(Application $app, $page = 1)
    {
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $pageCollection = new PostCollection($app);
        $pageCollection->sortBy('created_at', 'desc');
        $tagCollection = new PostCollection($app);
        $tagCollection->sortBy('created_at', 'desc');

        $config = $app['config'];
        $limit = $config->getProperty('PerPage');
        $offset = $limit * ($page - 1);
        $posts = $postCollection->findPosts($limit, $offset);
        $total = $posts->getTotalDocuments();
        $templateData = array(
            'config' => $config,
            'posts' => $posts,
            'pages' => $pageCollection->findPages(),
            'tags' => $tagCollection->findTags()
        );

        if ($total > $limit) {
            $pagination = new Pagination($app, 'home_page', array(), $page, $limit);
            $templateData['pagination'] = $pagination->getPagination($total);
        }

        return $templateData;
    }
}