<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;

use CodrPress\Model\PostCollection,
    CodrPress\Exception\PostNotFoundException;

class TagController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $pageCollection = new PostCollection($app);
        $pageCollection->sortBy('created_at', 'desc');

        $router->get('/tag/{tag}/', function ($tag) use ($app, $postCollection, $pageCollection) {
            $posts = $postCollection->findByTag($tag);

            if (count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('posts.twig', array(
                'config' => $app['config'],
                'tag' => $tag,
                'posts' => $posts,
                'pages' => $pageCollection->findPages()
            ));
        })->convert('tag', function ($tag) use ($app) {
            return $app['config']->sanitize($tag);
        })->bind('tag');

        return $router;
    }
}