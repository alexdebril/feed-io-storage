<?php

$loader = require __DIR__."/../vendor/autoload.php";
$loader->addPsr4('FeedIo\\Storage\\Tests\\', __DIR__.'/Storage');

date_default_timezone_set('UTC');
