<?php

namespace CodrPress\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface;

use CodrPress\Model\Post,
    CodrPress\Exception\PostNotFoundException;

class TagController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];


        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $router->get('/tag/{tag}/', function ($tag) use ($app) {
            $dm = $app['mango.dm'];

            $posts = Post::byTag($dm, $tag);
            $pages = Post::pages($dm);
            $tags = Post::tags($dm);


            if (count($posts) === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            return $app['twig']->render('posts.twig', array(
                'config' => $app['config'],
                'tag' => $tag,
                'posts' => $posts,
                'pages' => $pages->sort(['created_at' => -1]),
                'tags' => $tags
            ));
        })
            ->convert('tag', $sanitize)
            ->bind('tag');

        return $router;
    }
}