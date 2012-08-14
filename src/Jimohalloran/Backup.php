<?php

namespace Jimohalloran;
use Symfony\Component\Process\Process;

/**
 * Core backup class
 *
 * @author jim
 */
class Backup {
	const DATABASE_FOLDER = 'database';
	const FILE_FOLDER = 'files';
	
	protected $_config;
	
	protected $_tmpPath = false;
	protected $_tmpDbPath;
	protected $_tmpFilePath;
	protected $_tarball = false;
	
	public function __construct($yamlConfig) {
		$this->_config = $yamlConfig;
	}

	public function __destruct() {
		$this->_removeTmpFolder();
	}
	
	public function execute() {
		$this->_createTmpFolder();
		
		if (count($this->_config['database'])) {
			$this->_tmpDbPath = $this->_createFolder(self::DATABASE_FOLDER);
			foreach ($this->_config['database'] as $name => $connectionInfo) {
				$this->_mysqlDump($name, $connectionInfo);
			}
		}
		
		if (count($this->_config['files'])) {
			$this->_tmpFilePath = $this->_createFolder(self::FILE_FOLDER);
			foreach ($this->_config['files'] as $name => $path) {
				$this->_copyFiles($name, $path);
			}
		}
		
		if (count($this->_config['files']) || count($this->_config['database'])) {
			$tarFileName = $this->_createTarball($this->_config['name']);
		}
	}
	
	protected function _createTarball($siteName) {
		$this->_tarball = sys_get_temp_dir().'/'.$siteName.'-'.date('YmdHi').'.tar.gz';
		$cmd = 'tar zcf '. $this->_tarball . ' ' .$this->_tmpPath.'/';
		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
				throw new BackupException("Creating {$this->_tarball}: ".$process->getErrorOutput());
		}
	}
	
	protected function _copyFiles($name, $path) {
		$destDir = $this->_tmpFilePath.'/'.$name.'/';
		if (substr($path, -1) != '/') {
			$path .= '/';
		}
		$cmd = 'cp -a '.$path.'* '.$destDir;
		
		mkdir($destDir, 0700);
				
		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
				throw new BackupException("Error copying $name: ".$process->getErrorOutput());
		}
		
	}
	
	protected function _mysqlDump($name, $conn) {
		$cmd = 'mysqldump';
		$cmd .= ' -h '.$this->_elem($conn, 'hostname', 'localhost');
		$cmd .= ' -u '.$this->_elem($conn, 'username', 'root');
		$cmd .= array_key_exists('password', $conn) ? ' -p' .$conn['password'] : '';
		$cmd .= ' ' . $this->_elem($conn, 'database', '') . ' > ' . $this->_tmpDbPath . '/' . $name . '.sql';

		// Create a flag file during database backup.  e.g. Create a maintenance.
		// flag file to put Magento into maintenance mode.
		if (array_key_exists('touch', $conn)) {
			touch($conn['touch']);
		}
		
		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		
		if (array_key_exists('touch', $conn) && file_exists($conn['touch'])) {
			unlink($conn['touch']);
		}

		if (!$process->isSuccessful()) {
			throw new BackupException($process->getErrorOutput());
		}
	}
	
	protected function _elem($array, $key, $default) {
		if (array_key_exists($key, $array)) {
			return $array[$key];
		} else {
			return $default;
		}
	}
	
	protected function _createFolder($suffix) {
		$fullPath = $this->_tmpPath.'/'.$suffix;
		mkdir($fullPath, 0700);
		return $fullPath;
	}
	
	protected function _createTmpFolder() {
		do {
			$path = sys_get_temp_dir().'/'.$this->_config['name'].mt_rand(0, 9999999);
		} while (!mkdir($path, 0700));
		return $this->_tmpPath = $path;
	}
	
	protected function _removeTmpFolder() {
		if ($this->_tmpPath !== false) {
			$this->_rrmdir($this->_tmpPath);
		}
		
		if ($this->_tarball !== false && file_exists($this->_tarball)) {
			unlink($this->_tarball);
		}
	}
	
	protected function _rrmdir($dir) {
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file)) {
				$this->_rrmdir($file);
			} else {
				unlink($file);
			}
		}
		// Deal specifically with hidden files.
		foreach(glob($dir . '/.?*') as $file) {
			if (strpos($file, '..') === false) {
				if(is_dir($file)) {
					$this->_rrmdir($file);
				} else {
					unlink($file);
				}
			}
		}
		
		rmdir($dir);
	}
	
}
