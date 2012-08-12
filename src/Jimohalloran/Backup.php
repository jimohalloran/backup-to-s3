<?php

namespace Jimohalloran;
use Symfony\Component\Process\Process;

/**
 * Core backup class
 *
 * @author jim
 */
class Backup {

	protected $_config;
	
	protected $_tmpPath;
	protected $_tmpDbPath;
	
	public function __construct($yamlConfig) {
		$this->_config = $yamlConfig;
	}

	public function __destruct() {
		$this->_removeTmpFolder();
	}
	
	public function execute() {
		$this->_createTmpFolder();
		
		if (count($this->_config['database'])) {
			$this->_createDbFolder();
			foreach ($this->_config['database'] as $name => $connectionInfo) {
				$this->_mysqlDump($name, $connectionInfo);
			}
		}
	}
	
	protected function _mysqlDump($name, $conn) {
		$cmd = 'mysqldump';
		$cmd .= ' -h '.$this->_elem($conn, 'hostname', 'localhost');
		$cmd .= ' -u '.$this->_elem($conn, 'username', 'root');
		$cmd .= array_key_exists('password', $conn) ? ' -p' .$conn['password'] : '';
		$cmd .= ' ' . $this->_elem($conn, 'database', '') . ' > ' . $this->_tmpDbPath . '/' . $name . '.sql';

		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
				throw new BackupException($process->getErrorOutput());
		}
		print $process->getOutput();		
	}
	
	protected function _elem($array, $key, $default) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		} else {
			return $default;
		}
	}
	
	protected function _createDbFolder() {
		$this->_tmpDbPath = $this->_tmpPath.'/database';
		mkdir($this->_tmpDbPath, 0700);
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
