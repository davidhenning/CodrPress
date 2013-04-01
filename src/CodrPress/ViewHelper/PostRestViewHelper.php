<?php

namespace CodrPress\ViewHelper;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;

use CodrPress\Exception\PostNotFoundException;
use CodrPress\Model\Post;

class PostRestViewHelper
{

    protected function _getContentSkeleton($httpStatusCode)
    {
        return array(
            'meta' => array(
                'status' => $httpStatusCode,
                'msg' => Response::$statusTexts[$httpStatusCode]
            )
        );
    }

    public function getPostsContent(Application $app)
    {
        $config = $app['config'];
        $request = $app['request'];
        $limit = (int)$request->query->get('limit');
        $limit = ($limit > 0) ? $limit : $config->get('PerPage');
        $offset = (int)$request->query->get('offset');
        $posts = Post::posts();
        $total = $posts->count();

        $posts = $posts->limit($limit)->skip($offset)->sort(['created_at' => -1]);
        $content = $this->_getContentSkeleton(200);
        $found = $posts->count();

        if ($found > 0) {
            foreach ($posts as $post) {
                $content['response']['posts'][] = $post->getAttributes()->getArray();
            }
        }

        $content['response']['total'] = $total;
        $content['response']['found'] = $found;

        return $content;
    }

    public function getPostContent(Application $app, $id)
    {
        try {
            $post = Post::byId($id);

            if ($post->count() === 0) {
                throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
            }

            $content = $this->_getContentSkeleton(200);
            $content['response']['posts'][] = $post->first()->getAttributes()->getArray();
            $content['response']['total'] = 1;
            $content['response']['found'] = 1;
        } catch (\Exception $e) {
            $content = $this->_getContentSkeleton(404);
            $content['response']['posts'] = array();
            $content['response']['total'] = 0;
            $content['response']['found'] = 0;
        }

        return $content;
    }

    public function getPostUpdateContent(Application $app, $id = null)
    {
        $config = $app['config'];
        $request = $app['request'];

        try {
            $post = new Post();

            if (!is_null($id)) {
                $posts = Post::byId($id);

                if ($posts->count() === 0) {
                    throw new PostNotFoundException("The url '{$app['request']->getUri()}' does not exist!");
                }

                $post = $posts->first();
            }

            $payload = $config->sanitize($request->request->get('payload'));

            if (is_array($payload)) {
                foreach ($payload as $property => $value) {
                    $post->{$property} = $value;
                }
            }

            $post->store();
            $status = (!is_null($id)) ? 202 : 201;
            $content = $this->_getContentSkeleton($status);
            $content['response'] = array(
                'action' => (!is_null($id)) ? 'update' : 'create',
                'documentId' => (string)$post->_id,
                'documentUri' => "/post/{$post->_id}/"
            );
        } catch (\Exception $e) {
            $content = $this->_getContentSkeleton(404);
            $content['response']['posts'] = array();
            $content['response']['total'] = 0;
            $content['response']['found'] = 0;
        }

        return $content;
    }

    public function getPostDeleteContent(Application $app, $id)
    {
        try {
            $post = Post::byId($id)->first();
            $post->remove();
            $content = $this->_getContentSkeleton(202);
            $content['response'] = array(
                'action' => 'delete',
                'documentId' => $id
            );
        } catch (\Exception $e) {
            $content = $this->_getContentSkeleton(404);
            $content['response']['posts'] = array();
            $content['response']['total'] = 0;
            $content['response']['found'] = 0;
        }

        return $content;
    }

    public function getConvertMarkdownContent(Application $app)
    {
        $content = $this->_getContentSkeleton(200);
        $posts = Post::where([]);
        $posts->map(function ($document) {
            $document->store();
        });

        $content['status'] = 202;
        $content['response'] = array(
            'action' => 'convertMarkdown',
        );

        return $content;
    }
}
