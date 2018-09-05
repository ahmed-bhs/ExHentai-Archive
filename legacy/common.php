<?php

define('DS', DIRECTORY_SEPARATOR);

require_once(__DIR__.'/../vendor/autoload.php');

if(php_sapi_name() !== 'cli') {
	//Auth::doAuth();
}

require __DIR__.'/lib/rb.php';

$config = \Config::get();

\R::setup($config->db->dsn, $config->db->user, $config->db->pass);
\R::freeze(true);

?>
