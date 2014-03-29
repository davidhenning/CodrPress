<?php

namespace CodrPress;

abstract class Action {

    private $app;
    private $response;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    abstract protected function call();

    protected function getConfig() {
        return $this->app['config'];
    }

    protected function getRequest() {
        return $this->app['request'];
    }

    public function getResponse() {
        if ($this->response === null) {
            $this->response = $this->call();
        }

        return $this->response;
    }
} 