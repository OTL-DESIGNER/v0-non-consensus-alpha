<?php

require_once __DIR__ . '/vendor/autoload.php';

$zoom = new \Zoom\ZoomAPI();


var_dump( $zoom->createUser() );
exit();

