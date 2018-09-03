<?php

chdir(__DIR__.'/../');
require __DIR__.'/../common.php';

$api = new ApiHandler();
$api->handle($_REQUEST);

?>
