<?php

$config = new CodrPress\Config();
$config->setBaseDir(realpath(__DIR__));
$configFile = (isset($overrideConfigFile)) ? $config->getConfDir() . "/{$overrideConfigFile}" : $config->getConfDir() . '/codrpress.yml';
$config->addConfigFile($configFile);

$app = new CodrPress\Application($config);
$app->mount('', new CodrPress\Controller\HomepageController());
$app->mount('', new CodrPress\Controller\PostController());
$app->mount('', new CodrPress\Controller\TagController());
$app->mount('', new CodrPress\Controller\FeedController());

return $app;