<?php

namespace CodrPress\Controller;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use Silex\Application,
    Silex\ControllerProviderInterface;

use CodrPress\Model\Post;

class FeedController implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $router = $app['controllers_factory'];

        $router->get('/feed', function (Request $request) use ($app) {
            $dbConfig = $app['config']->getProperty('DbConfig');
            $posts = Post::posts()->sort(['created_at' => -1])->limit(20);

            $feed = new \SimpleXMLElement('<feed></feed>');
            $feed->addAttribute('xmlns', 'http://www.w3.org/2005/Atom');
            $feed->addChild('id', $request->getSchemeAndHttpHost().$request->getBaseUrl().'/');
            $feed->addChild('title', $dbConfig->blog_title);
            $feed->addChild('updated', $posts->head()->updated_at->format('c'));
            $feed->addChild('author')->addChild('name', $dbConfig->author_name);

            $link = $feed->addChild('link');
            $link->addAttribute('rel', 'self');
            $link->addAttribute('href', $request->getUri());

            $posts->each(function ($post) use ($app, $feed, $dbConfig) {
                $link = $app['url_generator']->generate('post', $post->getLinkParams());
                $entry = $feed->addChild('entry');
                $entry->addChild('title', $post->title);
                $entry->addChild('link')->addAttribute('href', $link);
                $entry->addChild('id', $link);
                $entry->addChild('updated', $post->updated_at->format('c'));
                $entry->addChild('published', $post->published_at->format('c'));
                $entry->addChild('author')->addChild('name', $dbConfig->author_name);
                $entry->addChild('content', $post->body_html)->addAttribute('type', 'html');
            });

            return new Response($feed->asXML(), 200, array('Content-type' => 'text/xml'));
        })
            ->bind('feed');

        return $router;
    }
}
