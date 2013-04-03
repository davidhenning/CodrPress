<?php

namespace CodrPress\Admin\Controller;

use CodrPress\Model\Post;
use Silex\Application;
use Silex\ControllerProviderInterface;

class PostController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $validateIdRegex = '[a-z0-9]{24}';

        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $router->get('/admin/posts', function() use ($app) {
            return $app['twig']->render('admin/posts.twig', array(
                'posts' => Post::where()
            ));
        })
            ->bind('admin_posts');

        $router->get('/admin/post/new', function() use ($app) {
            return $app['twig']->render('admin/post.twig');
        })
            ->bind('admin_post_new');

        $router->get('/admin/post/{id}', function($id) use ($app) {
            return $app['twig']->render('admin/post.twig', array(
                'post' => Post::find($id)->first()
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
