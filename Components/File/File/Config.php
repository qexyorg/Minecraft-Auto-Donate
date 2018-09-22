<?php
/**
 * File Config component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.0
 *
 */

namespace Alonity\Components\File;

use ConfigException;

require_once(__DIR__.'/ConfigException.php');

class Config {

	private $name = 'default';

	private $build = false;

	private $delete = false;

	private $path = __DIR__;

	private $params = [];

	private $files = [];

	private $error = null;

	private $info = 'Alonity config %NAME% | Updated: %DATE% %TIME%';

	public function __construct(){
		$this->path = basename(basename(basename(__DIR__)));
	}

	/**
	 * Выставляет информационную строку конфигурационного файла
	 *
	 * @param $string string
	 *
	 * @return Config();
	*/
	public function setInfo($string){
		$this->info = strval($string);

		return $this;
	}

	/**
	 * Выставляет директорию для работы с файлами конфигурации
	 *
	 * @param $path string
	 *
	 * @throws ConfigException
	 *
	 * @return Config();
	*/
	public function setPath($path){

		if(!is_string($path)){
			throw new ConfigException('path must be a string');
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * Устанавливает полный набор параметров конфига
	 *
	 * @param $data mixed
	 *
	 * @return Config();
	*/
	public function setData($data){
		$this->params = $data;

		return $this;
	}

	/**
	 * Устанавливает значения конфига
	 *
	 * @param $name mixed
	 * @param $value mixed
	 *
	 * @return Config();
	 */
	public function setParam($name, $value=null){
		$this->params[$name] = $value;

		return $this;
	}

	public function deleteParam($name){
		if(isset($this->params[$name])){
			unset($this->params[$name]);
		}

		return $this;
	}

	/**
	 * Устанавливает имя конфига
	 *
	 * @param $name string
	 *
	 * @return Config();
	 */
	public function name($name){
		$this->name = $name;

		return $this;
	}

	/**
	 * Параметр обновления конфига. Если конфиг не существует, он будет создан
	 *
	 * @return Config();
	 */
	public function build(){
		$this->build = true;

		return $this;
	}

	/**
	 * Параметр удаления существующего конфига
	 *
	 * @return Config();
	 */
	public function delete(){
		$this->delete = true;

		return $this;
	}

	public function get(){
		$filename = "{$this->path}/{$this->name}.php";

		$key = md5($filename);

		if(isset($this->files[$key])){
			return $this->files[$key];
		}

		if(!file_exists($filename)){
			return null;
		}

		$this->files[$key] = (include($filename));

		return $this->files[$key];
	}

	public function exists(){
		$get = $this->get();

		return (is_null($get)) ? false : true;
	}

	public function clearCache(){
		$key = md5("{$this->path}/{$this->name}.php");

		if(isset($this->files[$key])){
			unset($this->files[$key]);
		}

		return $this;
	}

	public function clearAllCache(){
		$this->files = [];

		return $this;
	}

	private function generateData(){

		$info = str_replace(['%NAME%', '%DATE%', '%TIME%'], [$this->name, date('d.m.Y'), date('H:i:s')], $this->info);

		$result = var_export($this->params, true);

		return "<?php // $info".PHP_EOL.PHP_EOL."return $result;".PHP_EOL.PHP_EOL."?>";
	}

	private function buildConfig(){
		$name = $this->name;
		$path = $this->path;

		$filename = "{$path}/{$name}.php";

		$key = md5($filename);

		if(!file_exists($path)){
			@mkdir($path, 0777, true);
		}

		if(!@file_put_contents($filename, $this->generateData(), LOCK_EX)){
			$this->error = 'error build config';

			return false;
		}

		$this->files[$key] = $this->params;

		return true;
	}

	private function deleteConfig(){
		$name = $this->name;
		$path = $this->path;

		$filename = "{$path}/{$name}.php";

		if(!file_exists($filename)){
			return true;
		}

		@unlink($filename);

		return true;
	}

	/**
	 * Ввыполняет ранее подготовленный запрос
	 *
	 * @return boolean
	*/
	public function execute(){
		if($this->build){
			if(!$this->buildConfig()){
				return false;
			}
		}

		if($this->delete){
			if(!$this->deleteConfig()){
				return false;
			}
		}

		return true;
	}

	/**
	 * Возвращает последнюю ошибку
	 *
	 * @return string
	 */
	public function getError(){
		return $this->error;
	}
}

?>