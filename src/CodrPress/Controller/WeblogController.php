<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;

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

        $router->get('/tag/{tag}/', function($tag) use($app, $postCollection, $pageCollection) {
            $posts = $postCollection->findByTag($tag);

            if(count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('posts.twig', array(
                'tag' => $tag,
                'posts' => $posts,
                'pages' => $pageCollection->findPages()
            ));
        })
        ->convert('tag', function($tag) use ($app) { return $app['config']->sanitize($tag); })
        ->bind('tag');

        return $router;
    }

}