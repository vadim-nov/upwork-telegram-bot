<?php

putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test');
date_default_timezone_set('Europe/London');
require dirname(__DIR__).'/../config/bootstrap.php';
