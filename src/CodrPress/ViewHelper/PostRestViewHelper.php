<?php

namespace CodrPress\ViewHelper;

use Silex\Application;

use CodrPress\Model\Post,
    CodrPress\Model\PostCollection;

class PostRestViewHelper
{

    protected function _getOutputSkeleton($app)
    {
        return array(
            'status' => 200,
            'time' => date('Y-m-d H:i:s'),
            'request' => array(
                'method' => 'GET',
                'url' => $app['request']->getPathInfo()
            )
        );
    }

    public function getPostsOutput(Application $app)
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

        $output = $this->_getOutputSkeleton($app);
        $output['response']['total'] = $postCollection->getTotalDocuments();
        $output['response']['found'] = $postCollection->getFoundDocuments();

        if (count($posts) > 0) {
            foreach ($posts as $post) {
                $output['response']['documents'][] = $post->getProperties();
            }
        }

        return $output;
    }

    public function getPostOutput(Application $app, $id)
    {
        $output = $this->_getOutputSkeleton($app);

        try {
            $post = new Post($app);
            $post->load($id);
            $output['response']['total'] = 1;
            $output['response']['found'] = 1;
            $output['response']['documents'][] = $post->getProperties();
        } catch (\Exception $e) {
            $output['status'] = 404;
            $output['response']['total'] = 0;
            $output['response']['found'] = 0;
            $output['response']['documents'] = array();
        }

        return $output;
    }

    public function getPostUpdateOutput(Application $app, $id = null)
    {
        $config = $app['config'];
        $request = $app['request'];
        $output = $this->_getOutputSkeleton($app);

        try {
            $post = new Post($app);

            if (!is_null($id)) {
                $post->load($id);
            }

            $payload = $config->sanitize($request->request->get('payload'));
            $post->updateProperties($payload);
            $post->store();
            $output['status'] = (!is_null($id)) ? 202 : 201;
            $output['response'] = array(
                'action' => (!is_null($id)) ? 'update' : 'create',
                'documentId' => $post->getId(),
                'documentUri' => "/post/{$post->getId()}/"
            );
        } catch (\Exception $e) {
            $output['status'] = 404;
            $output['response']['total'] = 0;
            $output['response']['found'] = 0;
            $output['response']['documents'] = array();
        }

        return $output;
    }

    public function getPostDeleteOutput(Application $app, $id)
    {
        $output = $this->_getOutputSkeleton($app);

        try {
            $post = new Post($app);
            $post->load($id);
            $post->remove();
            $output['status'] = 202;
            $output['response'] = array(
                'action' => 'delete',
                'documentId' => $id
            );
        } catch (\Exception $e) {
            $output['status'] = 404;
            $output['response']['total'] = 0;
            $output['response']['found'] = 0;
            $output['response']['documents'] = array();
        }

        return $output;
    }

    public function getConvertMarkdownOutput(Application $app)
    {
        $output = $this->_getOutputSkeleton($app);
        $posts = new PostCollection($app);
        $posts->find()->map(function ($document) use ($app) {
            $document->store();
        });

        $output['status'] = 202;
        $output['response'] = array(
            'action' => 'convertMarkdown',
        );

        return $output;
    }
}
