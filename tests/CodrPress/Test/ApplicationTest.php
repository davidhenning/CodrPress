<?php

use Silex\WebTestCase;

use CodrPress\Model\Post,
    CodrPress\Model\PostCollection;

class ApplicationTest extends WebTestCase
{

    public function createApplication()
    {
        $app = require realpath(__DIR__ . '/../../../app.php');
        $app['unittest'] = true;

        return $app;
    }

    public function tearDown()
    {
        parent::tearDown();
        $app = $this->app;

        $posts = new PostCollection($app);
        $posts->find(array('body' => 'test'))->remove();
    }

    public function testPosts()
    {
        $app = $this->app;
        $client = $this->createClient();

        // add test post
        $post = new Post($app);
        $post->setProperty('slugs', array('slug'));
        $post->setProperty('tags', array('Test'));
        $post->setProperty('title', 'test');
        $post->setProperty('body', 'test');
        $post->setProperty('status', 'published');
        $post->setProperty('disqus', false);
        $post->save();

        // home
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        // existing post
        $client->request('GET', date('/Y/m/d') . '/slug/');
        $this->assertTrue($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/20122/09/132/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/dfdf/09/13/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        $post->remove();
    }

    public function testTags()
    {
        $app = $this->app;
        $client = $this->createClient();

        // add test post
        $post = new Post($app);
        $post->setProperty('slugs', array('slug'));
        $post->setProperty('tags', array('Test'));
        $post->setProperty('title', 'test');
        $post->setProperty('body', 'test');
        $post->setProperty('status', 'published');
        $post->setProperty('disqus', false);
        $post->save();

        // existing tag
        $client->request('GET', '/tag/Test/');
        $this->assertTrue($client->getResponse()->isOk());

        // tag fail
        $client->request('GET', '/tag/MUHAHA/');
        $this->assertFalse($client->getResponse()->isOk());

        $post->remove();
    }

    public function testRestInterface()
    {
        $app = $this->app;
        $client = $this->createClient();

        // add test post
        $post = new Post($app);
        $post->setProperty('slugs', array('slug'));
        $post->setProperty('tags', array('Test'));
        $post->setProperty('title', 'REST test');
        $post->setProperty('body', 'test');
        $post->setProperty('status', 'published');
        $post->setProperty('disqus', false);
        $post->save();
        $id = $post->getId();

        $payload = json_encode(array('payload' => 'test'));

        $client->request('GET', "/posts/");
        $this->assertTrue($client->getResponse()->isOk());

        $client->request('GET', "/post/{$id}/");
        $this->assertTrue($client->getResponse()->isOk());

        $client->request('PUT', '/post/', array(), array(), array(), $payload);
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $newPostId = $response['response']['documentId'];

        $client->request('POST', "/post/{$newPostId}/", array(), array(), array(), $payload);
        $this->assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('DELETE', "/post/{$newPostId}/");
        $this->assertEquals(202, $client->getResponse()->getStatusCode());

        $client->request('GET', "/post/{$newPostId}/");
        $this->assertFalse($client->getResponse()->isOk());

        $post->remove();
    }
}