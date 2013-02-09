<?php

$config = new MongoAppKit\Config();
$config->setBaseDir(realpath(__DIR__));
$config->addConfigFile($config->getConfDir() . '/codrpress.yml');

$app = new CodrPress\Application($config);
$app->mount('', new CodrPress\Admin\Controller\PostController());

return $app;