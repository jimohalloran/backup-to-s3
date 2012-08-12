<?php

namespace Jimohalloran;
use Ulrichsg\Getopt;

/**
 * Command line parser
 *
 * @author jim
 */
class Cmdline {
	
	public $wantsHelp = false;
	public $yamlConfigFile;
	
	public function parse() {
		// Set defaults
		$this->yamlConfigFile = realpath(__DIR__.'/../../backup.yml');
		
		// Init option parser
		$getopt = new Getopt(array(
				array('h', 'help', Getopt::NO_ARGUMENT),
				array('c', 'config', Getopt::REQUIRED_ARGUMENT),
		));
		$getopt->parse();
		
		if ($getopt->getOption('help')) {
			$this->wantsHelp = true;
		}
		
		$file = $getopt->getOption('config');
		if ($file) {
			if (file_exists($file)) {
				if (is_readable($file)) {
					$this->yamlConfigFile = $file;
				} else {
					throw new \Jimohalloran\ArgsException("Config file '$file' is not readable.");
				}
			} else {
				throw new \Jimohalloran\ArgsException("Config file '$file' does not exist.");
			}
		}
		return $this;
	}
	
	public function displayHelp() {
		echo <<<HELP
Available command line arguments:
    -h or --help              Displays this message
    -c FILE or --config FILE  Use FILE as the YAML config file for thisbackup.

HELP;
	}
}
