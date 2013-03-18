<?php

namespace CodrPress\Helper;

use SilexMarkdown\Parser\ParserInterface;

class ContentHelper
{
    private static $markdown;

    public static function setMarkdown(ParserInterface $markdown)
    {
        self::$markdown = $markdown;
    }

    public static function getMarkdown()
    {
        if (!self::$markdown instanceof ParserInterface) {
            throw new \Exception('Please provide a markdown parser!');
        }

        return self::$markdown;
    }
}