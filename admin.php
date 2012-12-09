<?php

require realpath('vendor/autoload.php');

$app = require realpath('app_admin.php');
$app->run();