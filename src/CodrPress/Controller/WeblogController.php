<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use CodrPress\Exception\PostNotFoundException;

use CodrPress\Model\PostCollection;

class WeblogController implements ControllerProviderInterface {

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

        $router->get('/{year}/{month}/{day}/{slug}/', function($year, $month, $day, $slug) use($app, $postCollection, $pageCollection) {
            $posts = $postCollection->findBySlug($slug);

            if(count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('post.twig', array(
                'posts' => $posts,
                'pages' => $pageCollection->findPages()
            ));
        })
        ->assert('year', '\d{4}')
        ->assert('month', '\d{2}')
        ->assert('day', '\d{2}')
        ->convert('year', function($year) { return (int)$year; })
        ->convert('month', function($month) { return (int)$month; })
        ->convert('day', function($day) { return (int)$day; })
        ->convert('slug', function($slug) use ($app) { return $app['config']->sanitize($slug); })
        ->bind('post');

        return $router;
    }

}