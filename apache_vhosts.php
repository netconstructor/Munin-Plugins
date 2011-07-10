#!/usr/bin/php
<?php
/**
 * README
 * 
 * Add an additional line to each vhost you want to track with the domain name of the host
 * This will write a period for every hit to the vhost
 * CustomLog /web/logs/munin/example.com "."
 */

if(!array_key_exists('basedir', $_SERVER)) {
	echo "Add configuration for basedir:\n\t[apache_vhosts]\n\tenv.basedir /web/logs/munin\n";
	die(2);
}

$basedir = $_SERVER['basedir'];
$files = scandir($basedir);

// arg1 is blank or 'config' when munin runs it
$mode = array_key_exists(1, $argv) ? $argv[1] : '';


if($mode == 'config')
{
	echo 'graph_title Apache vhosts' . "\n";
		
	echo 'graph_info Shows the requests per second for each vhost.' . "\n";
	echo 'graph_vlabel requests / ${graph_period}' . "\n";
	echo 'graph_category apache' . "\n";
	echo 'graph_args --lower-limit 0' . "\n";
	echo 'graph_scale yes' . "\n";
	
	$first = TRUE;
	foreach($files as $domain)
	{
		if(substr($domain, 0, 1) != '.')
		{
			$key = generate_munin_key($domain);
			echo $key . '.label ' . $domain . "\n";
			echo $key . '.min 0' . "\n";
			echo $key . '.type DERIVE' . "\n";
			echo $key . '.draw ' . ($first ? 'AREA' : 'STACK') . "\n";
			$first = FALSE;
		}
	}
}
else
{
	foreach($files as $domain)
	{
		if(substr($domain, 0, 1) != '.')
		{
			$key = generate_munin_key($domain);
			$value = filesize($basedir . '/' . $domain) / 2;
			echo $key . '.value ' . $value . "\n";
		}
	}
}

function generate_munin_key($domain)
{
	return preg_replace(array('/\./', '/[^a-z0-9_]/'), array('_', ''), $domain);	
}
?>
