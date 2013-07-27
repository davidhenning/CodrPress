<?php

use Silex\WebTestCase;

use CodrPress\Model\Post;

class ApplicationTest extends WebTestCase
{

    public function createApplication()
    {
        $overrideConfigFile = 'test.yml';
        $app = require realpath(__DIR__ . '/../../../app.php');
        $app['unittest'] = true;

        return $app;
    }

    public function tearDown()
    {
        parent::tearDown();
        $posts = Post::where(['body' => 'test']);

        foreach ($posts as $post) {
            $post->remove();
        }
    }

    public function testPosts()
    {
        $app = $this->app;
        $dm = $app['mango.dm'];
        $client = $this->createClient();

        // add test post
        $post = new Post();
        $post->published_at = new \DateTime();
        $post->slugs = ['slug'];
        $post->tags = ['Test'];
        $post->title = 'test';
        $post->body = 'test';
        $post->status = 'published';
        $post->disqus = false;
        $post->store();

        // home
        $client->request('GET', '/');
        self::assertTrue($client->getResponse()->isOk());

        // existing post
        $client->request('GET', date('/Y/m/d') . '/test/');
        self::assertFalse($client->getResponse()->isOk());
        $client->request('GET', date('/Y/m/d') . '/slug/');
        self::assertTrue($client->getResponse()->isOk());

        // feed
        $client->request('GET', '/feed');
        self::assertTrue($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/20122/09/132/test/');
        self::assertFalse($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/dfdf/09/13/test/');
        self::assertFalse($client->getResponse()->isOk());

        $dm->remove($post);
    }

    public function testTags()
    {
        $app = $this->app;
        $dm = $app['mango.dm'];
        $client = $this->createClient();

        // add test post
        $post = new Post();
        $post->published_at = new \DateTime();
        $post->slugs = ['slug'];
        $post->tags = ['Test'];
        $post->title = 'test';
        $post->body = 'test';
        $post->status = 'published';
        $post->disqus = false;
        $dm->store($post);


        // existing tag
        $client->request('GET', '/tag/Test/');
        self::assertTrue($client->getResponse()->isOk());

        // tag fail
        $client->request('GET', '/tag/MUHAHA/');
        self::assertFalse($client->getResponse()->isOk());

        $dm->remove($post);
    }


    public function testRestInterface()
    {
        $app = $this->app;
        $dm = $app['mango.dm'];
        $client = $this->createClient();

        // add test post
        $post = new Post();
        $post->published_at = new \DateTime();
        $post->slugs = ['slug'];
        $post->tags = ['Test'];
        $post->title = 'test';
        $post->body = 'test';
        $post->status = 'published';
        $post->disqus = false;
        $dm->store($post);

        $id = (string)$post->_id;

        $payload = json_encode([
            'payload' => [
                'title' => 'json',
                'body' => 'test'
            ]
        ]);

        $client->request('GET', "/posts/");
        self::assertTrue($client->getResponse()->isOk());

        $client->request('GET', "/post/{$id}/");
        self::assertTrue($client->getResponse()->isOk());

        $client->request('PUT', '/post/', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertEquals(201, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $newPostId = $response['response']['documentId'];

        $client->request('POST', "/post/{$newPostId}/", [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('DELETE', "/post/{$newPostId}/");
        self::assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('GET', "/post/{$newPostId}/");
        self::assertFalse($client->getResponse()->isOk());

        $dm->remove($post);
    }
}