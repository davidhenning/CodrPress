<?php
use Silex\WebTestCase;

class ApplicationTest extends WebTestCase {

    public function createApplication() {
        $app = require realpath(__DIR__ . '/../../../app.php');

        return $app;
    }

    public function testPages() {
        $client = $this->createClient();

        // home
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        // post
        $client->request('GET', '/2012/09/12/slug/');
        $this->assertTrue($client->getResponse()->isOk());
    }
}