<?php

require realpath('vendor/autoload.php');

$console = new Symfony\Component\Console\Application();

$config = new CodrPress\Config();
$config->setBaseDir(realpath(__DIR__));
$configFile = (isset($overrideConfigFile)) ? $config->getConfDir() . "/{$overrideConfigFile}" : $config->getConfDir() . '/codrpress.yml';

if (is_readable($configFile)) {
    $config->addConfigFile($configFile);
    $app = new CodrPress\Application($config);
    $console->add(new CodrPress\Console\Command\User\Create($app));
}

$console->add(new \CodrPress\Console\Command\Config\Create());
$console->run();