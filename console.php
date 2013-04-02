<?php

require realpath('vendor/autoload.php');

$config = new CodrPress\Config();
$config->setBaseDir(realpath(__DIR__));
$configFile = (isset($overrideConfigFile)) ? $config->getConfDir() . "/{$overrideConfigFile}" : $config->getConfDir() . '/codrpress.yml';
$config->addConfigFile($configFile);

$app = new CodrPress\Application($config);
$console = new Symfony\Component\Console\Application();
$app->add(new CodrPress\Console\Command\User\Create($app));

$console->run();