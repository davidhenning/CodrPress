<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use CodrPress\Model\PostDocumentList;

class WeblogController implements ControllerProviderInterface {

    public function connect(Application $app) {
        $router = $app['controllers_factory'];
        $posts = new PostDocumentList($app);

        $router->get('/', function() use($app, $posts) {
            return $app['twig']->render('posts.twig', array(
                'posts' => $posts->findAll()
            ));
        })->bind('home');

        $router->get('/{year}/{month}/{day}/{slug}/', function($year, $month, $day, $slug) use($app, $posts) {
            return $app['twig']->render('post.twig', array(
                'posts' => $posts->findBySlug($slug)
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