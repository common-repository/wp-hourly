<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//if ( ! defined( 'WP_HOURLY_PATH' ) ) exit; // Exit if accessed directly

require_once( '../core/public.php');

	$task_id = $_REQUEST['task'];

	$task_time_records = wphGetTaskTimeRecords($task_id);

	echo '<pre>'.print_r($task_time_records, true).'</pre>';

	echo 'aaaa';

?>
