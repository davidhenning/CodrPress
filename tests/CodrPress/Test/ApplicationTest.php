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

        // post fail
        $client->request('GET', '/20122/09/132/slug/');
        $this->assertFalse($client->getResponse()->isOk());

        // post fail
        $client->request('GET', '/dfdf/09/13/slug/');
        $this->assertFalse($client->getResponse()->isOk());
    }
}