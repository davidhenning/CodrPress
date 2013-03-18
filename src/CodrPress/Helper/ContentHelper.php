<?php

namespace CodrPress\Helper;

class ContentHelper
{
    private static $markdown;

    public static function setMarkdown($markdown)
    {
        self::$markdown = $markdown;
    }

    public static function getMarkdown()
    {
        return self::$markdown;
    }
}