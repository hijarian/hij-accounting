<?php
/** hijarian 22.11.13 10:13 */

define('ROOT_DIR', realpath(__DIR__));

$f3=require(ROOT_DIR .'/lib/f3/base.php');

$f3->set('AUTOLOAD', 'src/;src/datatypes/');
$f3->set('db_path', ROOT_DIR . '/src/spending.db');
$f3->set('DEBUG',5);

/** @param Base $f3 */
$f3->route('GET /', function ($f3) {
	$f3->reroute('/spending');
});

$f3->route('GET /spending', 'SpendingController->showUi');

$f3->route('POST /add', 'SpendingController->addNew');

$f3->route('POST /correct', 'SpendingController->correctField');

$f3->route('GET /report', 'ReportController->histogram');

$f3->route('GET /ping', function () { return '';} );

$f3->set('ONERROR', function ($f3) {
	var_dump($f3->get('ERROR'));
});
ini_set('display_errors', 1);
error_reporting(-1);

$f3->run();
