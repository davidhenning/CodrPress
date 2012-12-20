<?php

namespace CodrPress\Admin\Controller;

use Silex\Application,
    Silex\ControllerProviderInterface,
    Silex\ControllerCollection;

use CodrPress\Model\Post,
    CodrPress\Model\PostCollection;

class PostController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $validateIdRegex = '[a-z0-9]{24}';

        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $router->get('/admin/posts', function() use ($app, $postCollection) {
            return $app['twig']->render('admin/posts.twig', array(
                'posts' => $postCollection->findPosts()
            ));
        })
            ->bind('admin_posts');

        $router->get('/admin/post/new', function() use ($app) {
            return $app['twig']->render('admin/post.twig');
        })
            ->bind('admin_post_new');

        $router->get('/admin/post/{id}', function($id) use ($app) {
            $post = new Post($app);
            $post->load($id);

            return $app['twig']->render('admin/post.twig', array(
                'post' => $post
            ));
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->bind('admin_post');

        $router->post('/admin/post', function() use($app) {
            return $app['twig']->render('admin/post.twig');
        })
            ->bind('admin_post_add');

        $router->post('/admin/post/{id}', function($id) use($app) {
            return $app['twig']->render('admin/post.twig');
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->bind('admin_post_edit');

        return $router;
    }
}
