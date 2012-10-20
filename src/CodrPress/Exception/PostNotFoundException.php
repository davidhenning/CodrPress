<?php

namespace CodrPress\Exception;

class PostNotFoundException extends \Exception {

    public function __construct($message = '', $code = 404, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
