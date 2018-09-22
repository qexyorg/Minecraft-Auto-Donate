<?php
/**
 * Permissions move component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 *
 */

namespace Alonity\Components\Permissions;

class PermissionsUpdateException extends \Exception {}

class Move {
	private $options = [];

	private $data = [];

	private $rootDir = null;

	public function __construct($options=null){
		if(is_null($options)){
			$options = [
				'storage' => 'file',
				'file' => [
					'path' => '/Uploads/permissions/',
				],
			];
		}

		$this->setOptions($options);

		$this->filename = $this->getRootDir().$this->options['path'];
	}

	/**
	 * Выставляет настройки работы с импортом
	 *
	 * @param $options array
	 *
	 * @return void
	*/
	public function setOptions($options){
		$this->options = array_replace_recursive($options, $this->options);
	}

	/**
	 * Возвращает корневую директорию
	 *
	 * @return string
	*/
	public function getRootDir(){
		if(!is_null($this->rootDir)){ return $this->rootDir; }

		$this->rootDir = dirname(dirname(dirname(__DIR__)));

		return $this->rootDir;
	}

	/**
	 * Устанавливает корневую директорию
	 *
	 * @param $dir string
	 *
	 * @return string
	 */
	public function setRootDir($dir){
		$this->rootDir = $dir;

		return $dir;
	}

	/**
	 * Создает хэш-сумму из параметров
	 *
	 * @param $params mixed
	 *
	 * @return string
	*/
	private function hashed($params){
		return md5(var_export($params, true));
	}

	/**
	 * Возвращает импортируемые данные или null в случае отсутствия таковых
	 *
	 * @param $name string
	 *
	 * @return array|null
	*/
	public function import($name){
		$name = $this->hashed($name);

		$filename = $this->getRootDir()."{$this->options['file']['path']}{$name}.php";

		if(isset($this->data[$name])){
			return $this->data[$name];
		}

		if(!file_exists($filename)){ return null; }

		$this->data[$name] = (include_once($filename));

		return $this->data[$name];
	}

	/**
	 * Экспортирует привилегии
	 *
	 * @param $name mixed
	 * @param $data array|null
	 *
	 * @return boolean
	 */
	public function export($name, $data=null){
		$hash = $this->hashed($name);

		if(is_null($data)){
			if(!isset($this->data[$hash])){
				$data = $this->import($name);
			}

			if(is_null($data)){
				return false;
			}
		}

		$filename = $this->getRootDir()."{$this->options['file']['path']}{$hash}.php";

		$data = "<?php // ".date("d.m.Y H:i:s").PHP_EOL.PHP_EOL;
		$data .= '	return '.var_export($data, true).';'.PHP_EOL.PHP_EOL;
		$data .= '?>';

		$dir = dirname($filename);

		if(!file_exists($dir) || !is_dir($dir)){
			@mkdir($dir, 0777, true);
		}

		$put = @file_put_contents($filename, $data, LOCK_EX);

		return ($put!==false);
	}

	/**
	 * Удаление группы привилегий
	 *
	 * @param $name mixed
	 *
	 * @return void
	*/
	public function remove($name){
		$hash = $this->hashed($name);

		$filename = $this->getRootDir()."{$this->options['file']['path']}{$hash}.php";

		if(file_exists($filename)){
			@unlink($filename);
		}
	}
}

?>