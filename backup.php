<?php
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

include __DIR__.'/vendor/autoload.php';

try {
	$config = Yaml::parse(__DIR__.'/backup.yml');
	var_dump($config);
} catch (ParseException $e) {
	echo "Error parsing configuration file: ".$e->getMessage()."\n";
	exit(1);
}