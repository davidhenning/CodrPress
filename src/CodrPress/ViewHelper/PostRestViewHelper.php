<?php

namespace CodrPress\ViewHelper;

use Silex\Application;

use Symfony\Component\HttpFoundation\Response;

use CodrPress\Model\Post,
    CodrPress\Model\PostCollection;

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
        $limit = ($limit > 0) ? $limit : $config->getProperty('PerPage');
        $offset = (int)$request->query->get('offset');

        $postCollection = new PostCollection($app);
        $postCollection->sortBy('created_at', 'desc');
        $postCollection->findPosts($limit, $offset, false);
        $posts = $postCollection->getProperties();

        $content = $this->_getContentSkeleton(200);

        if (count($posts) > 0) {
            foreach ($posts as $post) {
                $content['response']['posts'][] = $post->getArray();
            }
        }

        $content['response']['total'] = $postCollection->getTotalDocuments();
        $content['response']['found'] = $postCollection->getFoundDocuments();

        return $content;
    }

    public function getPostContent(Application $app, $id)
    {
        try {
            $post = new Post($app);
            $post->load($id);
            $content = $this->_getContentSkeleton(200);
            $content['response']['posts'][] = $post->getArray();
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
            $post = new Post($app);

            if (!is_null($id)) {
                $post->load($id);
            }

            $payload = $config->sanitize($request->request->get('payload'));
            $post->updateProperties($payload)->store();
            $status = (!is_null($id)) ? 202 : 201;
            $content = $this->_getContentSkeleton($status);
            $content['response'] = array(
                'action' => (!is_null($id)) ? 'update' : 'create',
                'documentId' => $post->getId(),
                'documentUri' => "/post/{$post->getId()}/"
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
            $post = new Post($app);
            $post->load($id);
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
        $posts = new PostCollection($app);
        $posts->find()->map(function ($document) use ($app) {
            $document->store();
        });

        $content['status'] = 202;
        $content['response'] = array(
            'action' => 'convertMarkdown',
        );

        return $content;
    }
}
