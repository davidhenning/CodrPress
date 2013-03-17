<?php

namespace CodrPress\ViewHelper;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;

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
        $dm = $app['mango.dm'];
        $config = $app['config'];
        $request = $app['request'];
        $limit = (int)$request->query->get('limit');
        $limit = ($limit > 0) ? $limit : $config->getProperty('PerPage');
        $offset = (int)$request->query->get('offset');
        $posts = Post::posts($dm);
        $total = $posts->count();

        $posts = $posts->limit($limit)->skip($offset)->sort(['created_at' => -1]);
        $content = $this->_getContentSkeleton(200);
        $found = $posts->count();

        if ($found > 0) {
            foreach ($posts as $post) {
                $content['response']['posts'][] = $post->getProperties()->getArray();
            }
        }

        $content['response']['total'] = $total;
        $content['response']['found'] = $found;

        return $content;
    }

    public function getPostContent(Application $app, $id)
    {
        $dm = $app['mango.dm'];

        try {
            $post = Post::byId($dm, $id)->head();
            $content = $this->_getContentSkeleton(200);
            $content['response']['posts'][] = $post->getProperties()->getArray();
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
        $dm = $app['mango.dm'];

        try {
            $post = new Post();

            if (!is_null($id)) {
                $post = Post::byId($dm, $id)->head();
            }

            $payload = $config->sanitize($request->request->get('payload'));
            print_r($request->getContent());

            foreach ($payload as $property => $value) {
                $post->{$property} = $value;
            }

            $dm->store($post);
            $status = (!is_null($id)) ? 202 : 201;
            $content = $this->_getContentSkeleton($status);
            $content['response'] = array(
                'action' => (!is_null($id)) ? 'update' : 'create',
                'documentId' => (string)$post->_id,
                'documentUri' => "/post/{$post->_id}/"
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
            $content = $this->_getContentSkeleton(404);
            $content['response']['posts'] = array();
            $content['response']['total'] = 0;
            $content['response']['found'] = 0;
        }

        return $content;
    }

    public function getPostDeleteContent(Application $app, $id)
    {
        $dm = $app['mango.dm'];

        try {
            $post = Post::byId($dm, $id)->head();
            $dm->remove($post);
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
        $dm = $app['mango.dm'];
        $content = $this->_getContentSkeleton(200);
        $posts = Post::where($dm, []);
        $posts->map(function ($document) use ($dm) {
            $dm->store($document);
        });

        $content['status'] = 202;
        $content['response'] = array(
            'action' => 'convertMarkdown',
        );

        return $content;
    }
}
