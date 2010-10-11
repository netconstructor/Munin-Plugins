#!/usr/bin/php
<?php
/**
 * README
 * 
 * Link this file as mysql_keycache_ratae and mysql_keycache in the plugins folder.
 * Define your database settings in the PDO variables.
 */

define('PDO_DSN', 'mysql:dbname=information_schema;host=localhost');
define('PDO_USER', 'munin');
define('PDO_PASS', 'munin');


// config or blank
$mode = array_key_exists(1, $argv) ? $argv[1] : '';

$section = explode('_', $argv[0]);
$section = array_key_exists(2, $section) ? $section[2] : '';

$variables = array(
	'key_reads' => 'The number of physical reads of a key block from disk.',
	'key_read_requests' => 'The number of requests to read a key block from the cache.',
	'key_writes' => 'The number of physical writes of a key block to disk.',
	'key_write_requests' => 'The number of requests to write a key block to the cache.'
);

$status = d()->query('SHOW STATUS');
$status->execute();
$info = array();
while($s = $status->fetch())
	if(in_array(strtolower($s['Variable_name']), array_keys($variables)))
		$info[strtolower($s['Variable_name'])] = $s['Value'];

if($section == 'rate')
{
	if($mode == 'config')
	{
		echo 'graph_title MySQL Key Read and Request Rate' . "\n";
		echo 'graph_vlabel per ${graph_period}' . "\n";
		echo 'graph_category mysql' . "\n";
		echo 'graph_period minute' . "\n";
		foreach($info as $k=>$n)
		{
			echo $k . '.label ' . $k . "\n";
			echo $k . '.type DERIVE' . "\n";
			echo $k . '.min 0' . "\n";
			echo $k . '.info ' . $variables[$k] . "\n";
		}
	}
	else
	{	
		foreach($info as $k=>$n)
			echo $k . '.value ' . $n . "\n";
	}
}
else
{
	if($mode == 'config')
	{
		echo 'graph_title MySQL Key Cache Effectiveness' . "\n";
		echo 'graph_category mysql' . "\n";
		echo 'graph_period minute' . "\n";
		echo 'key_read_cache.label Key Read Cache Miss %' . "\n";
		echo 'key_read_cache.info The number of physical key reads over the number of requests. Less than 1% is ideal.' . "\n";
		echo 'key_write_cache.label Key Write Cache Miss %' . "\n";
		echo 'key_write_cache.info The number of physical key writes over the number of requests.' . "\n";
	}
	else
	{
		echo 'key_read_cache.value ' . round(($info['key_reads'] * 100) / $info['key_read_requests'], 3) . "\n";
		echo 'key_write_cache.value ' . round(($info['key_writes'] * 100) / $info['key_write_requests'], 3) . "\n";
	}
}			
				

function d()
{
	static $DB;
	if(!isset($DB))
		$DB = new PDO(PDO_DSN, PDO_USER, PDO_PASS);
	return $DB;
}

?>