<?php

use Silex\WebTestCase;

use CodrPress\Model\Post,
    CodrPress\Model\PostCollection;

class ApplicationTest extends WebTestCase
{

    public function createApplication()
    {
        $overrideConfigFile = 'codrpress.yml.dist';
        $app = require realpath(__DIR__ . '/../../../app.php');
        $app['unittest'] = true;

        return $app;
    }

    public function tearDown()
    {
        parent::tearDown();
        $app = $this->app;
        $dm = $app['mango.dm'];
        $posts = Post::where($dm, ['body' => 'test']);

        foreach ($posts as $post) {
            $dm->remove($post);
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
        $dm->store($post);

        // home
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        // existing post
        $client->request('GET', date('/Y/m/d') . '/slug/');
        $this->assertTrue($client->getResponse()->isOk());

        // feed
        $client->request('GET', '/feed');
        $this->assertTrue($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/20122/09/132/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/dfdf/09/13/slug/');
        $this->assertFalse($client->getResponse()->isOk());

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
        $this->assertTrue($client->getResponse()->isOk());

        // tag fail
        $client->request('GET', '/tag/MUHAHA/');
        $this->assertFalse($client->getResponse()->isOk());

        $dm->remove($post);
    }

    /* deactivated due to bug when content-type json
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

        $payload = json_encode(array('payload' => 'test'));

        $client->request('GET', "/posts/");
        $this->assertTrue($client->getResponse()->isOk());

        $client->request('GET', "/post/{$id}/");
        $this->assertTrue($client->getResponse()->isOk());

        $client->request('PUT', '/post/', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        print_r($client->getResponse()->getContent());
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $newPostId = $response['response']['documentId'];

        $client->request('POST', "/post/{$newPostId}/", array(), array(), array(), $payload);
        $this->assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('DELETE', "/post/{$newPostId}/");
        $this->assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('GET', "/post/{$newPostId}/");
        $this->assertFalse($client->getResponse()->isOk());

        $dm->remove($post);
    }
    */

}