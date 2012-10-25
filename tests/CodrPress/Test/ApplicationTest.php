<?php

use Silex\WebTestCase;

use CodrPress\Model\Post;

class ApplicationTest extends WebTestCase {

    public function createApplication() {
        $app = require realpath(__DIR__ . '/../../../app.php');

        return $app;
    }

    public function testPages() {
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

        // existing tag
        $client->request('GET', '/tag/Test/');
        $this->assertTrue($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/20122/09/132/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        // tag fail
        $client->request('GET', '/tag/MUHAHA/');
        $this->assertFalse($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/dfdf/09/13/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        $post->delete();
    }
}