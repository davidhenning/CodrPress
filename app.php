<?php

$config = new MongoAppKit\Config();
$config->setBaseDir(realpath(__DIR__));
$config->addConfigFile($config->getConfDir() . '/mongoappkit.json');
$config->addConfigFile($config->getConfDir() . '/codrpress.json');

$app = new CodrPress\Application($config);
$app->mount('', new CodrPress\Controller\HomepageController());
$app->mount('', new CodrPress\Controller\PostController());
$app->mount('', new CodrPress\Controller\TagController());

return $app;