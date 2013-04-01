<?php

namespace CodrPress\Model;

use Silex\Application;

use Mango\DocumentInterface,
    Mango\Document;

use Collection\MutableMap;

use CodrPress\Helper\ContentHelper;

class Post implements DocumentInterface
{
    use Document;

    private function addFields()
    {
        $this->addField(
            'created_at',
            [
                'type' => 'DateTime',
                'index' => true,
                'default' => 'now'
            ]
        );

        $this->addField(
            'updated_at',
            [
                'type' => 'DateTime',
                'index' => true,
                'default' => 'now'
            ]
        );

        $this->addField(
            'published_at',
            [
                'type' => 'DateTime',
                'index' => true
            ]
        );

        $this->addField('title', ['default' => '']);

        $this->addField('body', ['default' => '']);

        $this->addField('body_html', ['default' => '']);

        $this->addField('status', ['default' => '']);

        $this->addField('disqus', ['default' => false]);

        $this->addField(
            'slugs',
            [
                'default' => [],
                'index' => true
            ]
        );

        $this->addField(
            'tags',
            [
                'default' => [],
                'index' => true
            ]
        );
    }

    public static function posts($published = true)
    {
        $conditions = ['published_at' => array('$ne' => null)];

        if ($published === true) {
            $conditions['status'] = 'published';
        }

        return self::where($conditions);
    }

    public static function pages()
    {
        return self::where(['published_at' => null, 'status' => 'published']);
    }

    public static function tags()
    {
        $posts = self::posts();
        $tags = new MutableMap();

        $posts->each(function ($post) use ($tags) {
            if(isset($post->tags) && !empty($post->tags)) {
                foreach($post->tags as $tag) {
                    if(!isset($tags->{$tag})) {
                        $list = new MutableMap();
                        $tags->{$tag} = $list->assign(array('name' => $tag, 'count' => 0));
                    }

                    $tags->{$tag}->count += 1;
                }
            }
        });

        return $tags;
    }

    public static function bySlug($year, $month, $day, $slug)
    {
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = $start + 60 * 60 * 24;

        $conditions = [
            'created_at' => [
                '$gt' => new \MongoDate($start),
                '$lt' => new \MongoDate($end)
            ],
            'slugs' => ['$in' => [$slug]],
            'status' => 'published'
        ];

        return self::where($conditions)->limit(1);
    }

    public static function byTag($tag)
    {
        return self::where(['tags' => $tag, 'status' => 'published']);
    }

    public static function byId($id)
    {
        return self::where(['_id' => new \MongoId($id)]);
    }

    public static function getCollectionName()
    {
        return 'posts';
    }

    private function prepare()
    {
        //transform Markdown
        $this->body_html = ContentHelper::getMarkdown()->transform($this->body);

        // create slugs
        $this->slugs = $this->createSlugs($this->slugs, $this->title);
    }

    private function createSlugs($slugs, $title)
    {
        if (!is_array($slugs)) {
            $slugs = array($slugs);
        }

        $slugs = array_merge($slugs, array($title));

        $slugs = new MutableMap($slugs);
        $slugs->map(function($value) {
            $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
            $slug = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $slug);
            $slug = strtolower(trim($slug, '-'));
            $slug = preg_replace("/[\/_| -]+/", '-', $slug);

            return $slug;
        })->unique();

        return $slugs->getArray();
    }

    public function getLinkParams()
    {
        $timestamp = $this->created_at->getTimestamp();

        $slugs = $this->slugs;

        return [
            'year' => date('Y', $timestamp),
            'month' => date('m', $timestamp),
            'day' => date('d', $timestamp),
            'slug' => end($slugs)
        ];
    }
}
