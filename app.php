<?php

use MongoAppKit\Config;

$config = new Config();
$config->setBaseDir(realpath(__DIR__));
$config->addConfigFile($config->getConfDir() . '/mongoappkit.json');
$config->addConfigFile($config->getConfDir() . '/codrpress.json');

$app = new CodrPress\Application($config);
$app->mount('', new CodrPress\Controller\WeblogController());

return $app;