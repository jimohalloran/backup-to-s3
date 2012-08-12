<?php

namespace Jimohalloran;

/**
 * Core backup class
 *
 * @author jim
 */
class Backup {

	protected $_config;
	
	protected $_tmpPath;
	
	public function __construct($yamlConfig) {
		$this->_config = $yamlConfig;
	}

	public function __destruct() {
		$this->_removeTmpFolder();
	}
	
	public function execute() {
		$this->_createTmpFolder();
	}
	
	protected function _createTmpFolder() {
		do {
			$path = sys_get_temp_dir().'/'.$this->_config['name'].mt_rand(0, 9999999);
		} while (!mkdir($path, 0700));
		return $this->_tmpPath = $path;
	}
	
	protected function _removeTmpFolder() {
		$this->_rrmdir($this->_tmpPath);
	}
	
	protected function _rrmdir($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file)) {
				$this->_rrmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dir);
	}
	
}
