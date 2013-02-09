<?php

$config = new MongoAppKit\Config();
$config->setBaseDir(realpath(__DIR__));
$config->addConfigFile($config->getConfDir() . '/codrpress.yml');

$app = new CodrPress\Application($config);
$app->mount('', new CodrPress\Controller\HomepageController());
$app->mount('', new CodrPress\Controller\PostController());
$app->mount('', new CodrPress\Controller\TagController());
$app->mount('', new CodrPress\Controller\FeedController());

return $app;