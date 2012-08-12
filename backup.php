<?php
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

include __DIR__.'/vendor/autoload.php';

try {
	$cmdLine = new \Jimohalloran\Cmdline();
	$cmdLine->parse();
} catch (\Jimohalloran\ArgsException $e) {
	echo "Error parsing command line: ". $e->getMessage()."\n\n";
	$cmdLine->displayHelp();
	exit(1);
}

if ($cmdLine->wantsHelp) {
	$cmdLine->displayHelp();
	exit;
}

try {
	$config = Yaml::parse($cmdLine->yamlConfigFile);
	var_dump($config);
} catch (ParseException $e) {
	echo "Error parsing configuration file: ".$e->getMessage()."\n";
	exit(1);
}

try {
	$backup = new \Jimohalloran\Backup($config);
	$backup->execute();
} catch (\Jimohalloran\BackupException $e) {
	echo "Error performing backup: ".$e->getMessage()."\n";
	exit(1);
}