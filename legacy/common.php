<?php

define('DS', DIRECTORY_SEPARATOR);

require_once(__DIR__.'/../vendor/autoload.php');

if(php_sapi_name() !== 'cli') {
	//Auth::doAuth();
}

require __DIR__.'/lib/rb.php';

$config = \Config::get();

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env));
$kernel = new \App\Kernel($env, $debug);
$kernel->boot();
/** @var \App\Service\LegacyBridgeService $bridgeService */
$bridgeService = $kernel->getContainer()->get('legacy.bridge');
Log::$monolog = $bridgeService->getLogger();
/** @var \App\Service\ExHentaiBrowserService $exClient */
$exClient = $bridgeService->getBrowser();



\R::setup($config->db->dsn, $config->db->user, $config->db->pass);
\R::freeze(true);

?>
