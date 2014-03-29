<?php

namespace CodrPress;

use Silex\Application;

abstract class Presenter {

    private $app;
    private $dto;

    public function __construct(Application $app, $dto) {
        $this->app = $app;
        $this->dto = $dto;
    }

    protected function getDto() {
        return $this->dto;
    }
} 