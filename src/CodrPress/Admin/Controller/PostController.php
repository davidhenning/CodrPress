<?php

namespace CodrPress\Admin\Controller;

use CodrPress\Helper\AuthHelper;
use CodrPress\Model\Post;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class PostController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];
        $validateIdRegex = '[a-z0-9]{24}';
        $app['config']->set('codrpress.views.activeNavItem', 'posts');
        $authHelper = new AuthHelper();
        $authHelper->registerAuthErrorHandler($app);
        $login = $authHelper->getAuthCallable($app);

        $sanitize = function ($id) use ($app) {
            return $app['config']->sanitize($id);
        };

        $router->get('/admin/posts', function() use ($app) {
            return $app['twig']->render('admin/posts.haml', [
                'posts' => Post::where()->sort(['created_at' => -1])
            ]);
        })
            ->bind('admin_posts')
            ->before($login);

        $router->get('/admin/post/new', function() use ($app) {
            return $app['twig']->render('admin/post.haml', [
                'post' => new Post()
            ]);
        })
            ->bind('admin_post_new')
            ->before($login);

        $router->get('/admin/post/{id}', function($id) use ($app) {
            return $app['twig']->render('admin/post.haml', [
                'post' => Post::find($id)->first()
            ]);
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->bind('admin_post')
            ->before($login);

        $router->post('/admin/post', function(Request $request) use($app) {
            $data = $request->request->get('post');
            $post = new Post($data);
            $post->store();

            return $app->redirect($app['url_generator']->generate('admin_posts'));
        })
            ->bind('admin_post_add')
            ->before($login);

        $router->post('/admin/post/{id}', function(Request $request, $id) use($app) {
            $data = $request->request->get('post');
            $post = Post::find($id)->first();

            $post->update($data);
            $post->store();

            return $app->redirect($app['url_generator']->generate('admin_posts'));
        })
            ->assert('id', $validateIdRegex)
            ->convert('id', $sanitize)
            ->bind('admin_post_edit')
            ->before($login);

        return $router;
    }
}
