<?php

namespace CodrPress\Model;

use Silex\Application;

use Mango\DocumentInterface;
use Mango\Document;

class Config implements DocumentInterface
{
    use Document;

    public $blog_title;
    public $author_name;
}
