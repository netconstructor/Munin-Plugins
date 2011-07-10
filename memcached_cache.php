#!/usr/bin/php
<?php

$mode = array_key_exists(1, $argv) ? $argv[1] : '';

$host = array_key_exists('host', $_SERVER) ? $_SERVER['host'] : 'localhost';
$port = array_key_exists('port', $_SERVER) ? $_SERVER['port'] : '11211';

$stats = shell_exec('echo "stats" | nc ' . $host . ' ' . $port . ' | grep "get_hits\|get_misses\|delete_hits\|delete_misses"');
$lines = explode("\n", $stats);
$stats = array();
foreach($lines as $l)
	if(trim($l) != '')
		$stats[] = explode(' ', $l);

if($mode == 'config')
{
	echo 'graph_title Memcached - Cache Info' . "\n";
	echo 'graph_info Shows the volume of cache hits and misses' . "\n";
	echo 'graph_vlabel /second' . "\n";
	echo 'graph_category memcache' . "\n";
	echo 'graph_args --lower-limit 0' . "\n";
	echo 'graph_scale yes' . "\n";
	$first = TRUE;
echo 'get_hits.label get_hits
get_hits.type COUNTER
get_hits.draw AREA
get_misses.label get_misses
get_misses.type COUNTER
get_misses.draw STACK
';
}
else
{
	foreach($stats as $s)
	{
		echo $s[1] . '.value ' . $s[2] . "\n";
	}
}
