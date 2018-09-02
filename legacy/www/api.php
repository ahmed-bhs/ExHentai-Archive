<?php

chdir(__DIR__.'/../');
require 'common.php';

$api = new ApiHandler();
$api->handle($_REQUEST);

?>
