<?php
/**
 * File Upload component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.1
 *
 */

namespace Alonity\Components\File;

use UploadException;

require_once(__DIR__.'/UploadException.php');

class Upload {
	const FILES_STRATEGY = 0x00;
	const DIR_STRATEGY = 0x01;
	const URL_STRATEGY = 0x02;

	private $strategy = 0x00;

	private $files = null;

	private $extensions = [];

	private $maxFiles = 0;

	private $minFiles = 0;

	private $maxFileSize = 0;

	private $minFileSize = 0;

	private $maxGlobalSize = 0;

	private $minGlobalSize = 0;

	private $path = '';

	private $name = null;

	private $names = [];

	private $paths = [];

	private $error = null;

	private $default_timeout = 3;

	public function __construct(){
		$this->path = __DIR__.'/';
	}

	/**
	 * Выставляет пути к загружаемым файлам или массив загружаемых файлов
	 *
	 * @param $files array | string
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	*/
	public function files($files){
		if(!is_array($files) && !is_string($files)){
			throw new UploadException('files must be array');
		}

		$this->files = $files;

		return $this;
	}

	/**
	 * Устанавливает стратегию загрузки файлов
	 *
	 * @param $type int
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	*/
	public function setStrategy($type){
		if(!is_int($type)){
			throw new UploadException("Strategy must be integer");
		}

		if($type!==self::FILES_STRATEGY &&
			$type!==self::DIR_STRATEGY &&
			$type!==self::URL_STRATEGY){
			throw new UploadException("Undefined strategy");
		}

		$this->strategy = $type;

		return $this;
	}

	/**
	 * Устанавливает допустимые для загрузки форматы
	 *
	 * @param $extensions array | string
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	 */
	public function extensions($extensions){

		if(is_string($extensions)){
			$this->extensions[] = $extensions;
		}elseif(is_array($extensions)){
			$this->extensions = array_replace_recursive($this->extensions, $extensions);
		}else{
			throw new UploadException('extensions must be array or string');
		}

		return $this;
	}

	/**
	 * Устанавливает максимально допустимое кол-во загружаемых файлов
	 *
	 * @param $num integer
	 *
	 * @return Upload();
	 */
	public function maxFiles($num){

		$this->maxFiles = intval($num);

		return $this;
	}

	/**
	 * Устанавливает минимально допустимое кол-во загружаемых файлов
	 *
	 * @param $num integer
	 *
	 * @return Upload();
	 */
	public function minFiles($num){

		$this->minFiles = intval($num);

		return $this;
	}

	/**
	 * Устанавливает максимальный размер загружаемого файла
	 *
	 * @param $size integer
	 *
	 * @return Upload();
	 */
	public function maxFileSize($size){

		$this->maxFileSize = intval($size);

		return $this;
	}

	/**
	 * Устанавливает минимальный размер загружаемого файла
	 *
	 * @param $size integer
	 *
	 * @return Upload();
	 */
	public function minFileSize($size){

		$this->minFileSize = intval($size);

		return $this;
	}

	/**
	 * Устанавливает общий максимальный размер загружаемых файлов
	 *
	 * @param $size integer
	 *
	 * @return Upload();
	 */
	public function maxGlobalSize($size){

		$this->maxGlobalSize = intval($size);

		return $this;
	}

	/**
	 * Устанавливает общий минимальный размер загружаемых файлов
	 *
	 * @param $size integer
	 *
	 * @return Upload();
	 */
	public function minGlobalSize($size){

		$this->minGlobalSize = intval($size);

		return $this;
	}

	/**
	 * Устанавливает директорию в которую будут заргужены файлы
	 *
	 * @param $path string
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	 */
	public function setUploadPath($path){
		if(!is_string($path)){
			throw new UploadException('Path must be a string');
		}

		if(mb_substr($path, -1, null, 'UTF-8')!='/'){
			$path .= '/';
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * Устанавливает новое имя для файла
	 *
	 * @param $name string
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	 */
	public function setName($name){
		if(!is_string($name)){
			throw new UploadException('Name must be a string');
		}

		$this->name = $name;

		return $this;
	}

	private function random($min, $max){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

		$split = preg_split('//', $chars, NULL, PREG_SPLIT_NO_EMPTY);

		$len = strlen($chars)-1;

		$str = '';

		for($i=0;$i<mt_rand($min, $max);$i++){
			$str .= $split[mt_rand(0, $len)];
		}

		return $str;
	}

	/**
	 * Устанавливает новое имя для файла
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @throws UploadException
	 *
	 * @return Upload();
	 */
	public function setRandomName($min=16, $max=16){

		$min = intval($min);
		$max = intval($max);

		if($min<=0){
			throw new UploadException('Random name param min must be more then 0');
		}

		if($min>$max){
			throw new UploadException('Random name param min can\'t be more then max');
		}

		$this->name = $this->random($min, $max);

		return $this;
	}

	private function filterFiles($files){
		if(!is_array($files)){
			return false;
		}

		$arraySize = null;

		if(!isset($files['name']) || empty($files['name'])){
			return false;
		}

		if(!isset($files['type']) || empty($files['type'])){
			return false;
		}

		if(!isset($files['tmp_name']) || empty($files['tmp_name'])){
			return false;
		}

		if(!isset($files['error'])){
			return false;
		}

		if(!isset($files['size']) || empty($files['size'])){
			return false;
		}

		$stack = [];

		if(is_array($files['name'])){
			foreach($files['name'] as $k => $v){

				if(!isset($files['error'][$k]) || !empty($files['error'][$k])){
					$this->error = 'Error number: '.$files['error'][$k];

					return false; break;
				}

				if(!isset($files['name'][$k]) || empty($files['name'][$k])){
					$this->error = 'name key is not set';

					return false; break;
				}

				if(!isset($files['type'][$k]) || empty($files['type'][$k])){
					$this->error = 'type key is not set';

					return false; break;
				}

				if(!isset($files['tmp_name'][$k]) || empty($files['tmp_name'][$k])){
					$this->error = 'tmp_name key is not set';

					return false; break;
				}

				if(!isset($files['size'][$k]) || empty($files['size'][$k])){
					$this->error = 'size key is not set';

					return false; break;
				}

				$stack[] = [
					'name' => $files['name'][$k],
					'type' => $files['type'][$k],
					'tmp_name' => $files['tmp_name'][$k],
					'error' => $files['error'][$k],
					'size' => $files['size'][$k],
				];
			}

		}else{

			if(!isset($files['error']) || !empty($files['error'])){
				$this->error = 'Error number: '.$files['error'];

				return false;
			}

			if(!isset($files['name']) || empty($files['name'])){
				$this->error = 'name key is not set';

				return false;
			}

			if(!isset($files['type']) || empty($files['type'])){
				$this->error = 'type key is not set';

				return false;
			}

			if(!isset($files['tmp_name']) || empty($files['tmp_name'])){
				$this->error = 'tmp_name key is not set';

				return false;
			}

			if(!isset($files['size']) || empty($files['size'])){
				$this->error = 'size key is not set';

				return false;
			}

			$stack[] = [
				'name' => $files['name'],
				'type' => $files['type'],
				'tmp_name' => $files['tmp_name'],
				'error' => $files['error'],
				'size' => $files['size'],
			];
		}

		return $stack;
	}

	private function filesStrategy(){
		$files = $this->filterFiles($this->files);

		if($files===false){
			return false;
		}

		$numFiles = sizeof($files);

		if($this->maxFiles>0 && $numFiles>$this->maxFiles){
			$this->error = 'Max files is '.$this->maxFiles;

			return false;
		}

		if($this->minFiles>0 && $numFiles<$this->minFiles){
			$this->error = 'Min files is '.$this->maxFiles;

			return false;
		}

		$globalSize = 0;

		foreach($files as $k => $v){
			$size = intval($v['size']);

			$globalSize += $size;

			if($this->maxFileSize>0 && $size>$this->maxFileSize){
				$this->error = 'Max file size is '.$this->maxFileSize.' bytes';

				return false; break;
			}

			if($this->minFileSize>0 && $size<$this->minFileSize){
				$this->error = 'Min file size is '.$this->minFileSize.' bytes';

				return false; break;
			}

			if($this->maxGlobalSize>0 && $globalSize>$this->maxGlobalSize){
				$this->error = 'Max files size is '.$this->maxGlobalSize.' bytes';

				return false; break;
			}

			if($this->minGlobalSize>0 && $globalSize>$this->minGlobalSize){
				$this->error = 'Min files size is '.$this->minGlobalSize.' bytes';

				return false; break;
			}

			$pathinfo = pathinfo($v['name']);

			if(!empty($this->extensions) && !in_array($pathinfo['extension'], $this->extensions)){
				$this->error = 'Extension not accepted';

				return false; break;
			}

			$newname = (is_null($this->name)) ? basename($v['name']) : $this->name.'.'.$pathinfo['extension'];

			if(!move_uploaded_file($v['tmp_name'], $this->path.$newname)){
				$this->error = error_get_last();

				return false; break;
			}

			$this->names[] = $newname;

			$this->paths[] = $this->path.$newname;
		}

		return true;
	}

	private function dirStrategy(){

		if(!is_array($this->files)){
			$this->files = [$this->files];
		}

		$globalSize = 0;

		foreach($this->files as $path){

			if(!file_exists($path)){
				$this->error = 'File not found';

				return false; break;
			}

			$size = filesize($path);

			$globalSize += $size;

			$pathinfo = pathinfo($path);

			if($this->maxFileSize>0 && $size>$this->maxFileSize){
				$this->error = 'Max file size is '.$this->maxFileSize.' bytes';

				return false; break;
			}

			if($this->minFileSize>0 && $size<$this->minFileSize){
				$this->error = 'Min file size is '.$this->minFileSize.' bytes';

				return false; break;
			}

			if($this->maxGlobalSize>0 && $globalSize>$this->maxGlobalSize){
				$this->error = 'Max files size is '.$this->maxGlobalSize.' bytes';

				return false; break;
			}

			if($this->minGlobalSize>0 && $globalSize>$this->minGlobalSize){
				$this->error = 'Min files size is '.$this->minGlobalSize.' bytes';

				return false; break;
			}

			if(!empty($this->extensions) && !in_array($pathinfo['extension'], $this->extensions)){
				$this->error = 'Extension not accepted';

				return false; break;
			}

			$newname = (is_null($this->name)) ? basename($path) : $this->name.'.'.$pathinfo['extension'];

			if(!@rename($path, $this->path.$newname)){
				$this->error = 'error move file';

					return false; break;
			}

			$this->names[] = $newname;

			$this->paths[] = $this->path.$newname;
		}

		return true;
	}

	private function urlStrategy(){
		if(!is_array($this->files)){
			$this->files = [$this->files];
		}

		if(!function_exists('curl_init')){

		}else{

		}

		$globalSize = 0;

		foreach($this->files as $path){

			if(function_exists('curl_init')){
				$file = @file_get_contents($path);
			}else{
				$file = @$this->getFromUrl($path);
			}

			if($file===false){
				return false; break;
			}

			$size = mb_strlen($path, 'UTF-8');

			$globalSize += $size;

			$pathinfo = pathinfo($path);

			if($this->maxFileSize>0 && $size>$this->maxFileSize){
				$this->error = 'Max file size is '.$this->maxFileSize.' bytes';

				return false; break;
			}

			if($this->minFileSize>0 && $size<$this->minFileSize){
				$this->error = 'Min file size is '.$this->minFileSize.' bytes';

				return false; break;
			}

			if($this->maxGlobalSize>0 && $globalSize>$this->maxGlobalSize){
				$this->error = 'Max files size is '.$this->maxGlobalSize.' bytes';

				return false; break;
			}

			if($this->minGlobalSize>0 && $globalSize>$this->minGlobalSize){
				$this->error = 'Min files size is '.$this->minGlobalSize.' bytes';

				return false; break;
			}

			if(!empty($this->extensions) && !in_array($pathinfo['extension'], $this->extensions)){
				$this->error = 'Extension not accepted';

				return false; break;
			}

			$newname = (is_null($this->name)) ? basename($path) : $this->name.'.'.$pathinfo['extension'];

			if(!@rename($path, $this->path.$newname)){
				$this->error = 'error move file';

				return false; break;
			}

			$this->names[] = $newname;

			$this->paths[] = $this->path.$newname;
		}

		return true;
	}

	public function getFromUrl($url, $params=[]){

		if(!isset($params['post']) || !is_bool($params['post'])){ $params['post'] = false; }

		if(!isset($params['timeout']) || intval($params['timeout'])<=0){ $params['timeout'] = $this->default_timeout; }

		if(!isset($params['postfields']) || !is_array($params['postfields'])){ $params['postfields'] = []; }

		$useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

		$c = curl_init($url);

		$headers = [];

		if(isset($params['file']) && $params['file']){
			$headers = ['Content-Type:multipart/form-data'];
			$params['post'] = true;
		}

		curl_setopt_array($c, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_POST => $params['post'],
			CURLOPT_POSTFIELDS => $params['postfields'],
			CURLOPT_CONNECTTIMEOUT => $params['timeout'],
			CURLOPT_TIMEOUT => $params['timeout'],
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_USERAGENT => $useragent
		]);

		$result = curl_exec($c);

		curl_close($c);

		return ($result!==false) ? $result : false;
	}

	/**
	 * Запускает процесс загрузки файлов на сервер
	 *
	 * @return boolean
	*/
	public function execute(){

		if($this->strategy===self::FILES_STRATEGY){
			return $this->filesStrategy();
		}elseif($this->strategy===self::DIR_STRATEGY){
			return $this->dirStrategy();
		}elseif($this->strategy===self::URL_STRATEGY){
			return $this->urlStrategy();
		}else{
			return false;
		}
	}

	/**
	 * Возвращает массив имен загруженных файлов
	 *
	 * @return array
	*/
	public function getNames(){
		return $this->names;
	}

	/**
	 * Возвращает массив полных путей до файлов
	 *
	 * @return array
	 */
	public function getPaths(){
		return $this->paths;
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