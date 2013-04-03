<?php

namespace CodrPress\Controller;

use CodrPress\Exception\PostNotFoundException;
use CodrPress\Model\Post;
use Silex\Application;
use Silex\ControllerProviderInterface;

class TagController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];


        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $router->get('/tag/{tag}/', function ($tag) use ($app) {
            $posts = Post::byTag($tag);
            $pages = Post::pages();
            $tags = Post::tags();


            if (count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('posts.haml', [
                'config' => $app['config'],
                'tag' => $tag,
                'posts' => $posts,
                'pages' => $pages->sort(['created_at' => -1]),
                'tags' => $tags
            ]);
        })
            ->convert('tag', $sanitize)
            ->bind('tag');

        return $router;
    }
}