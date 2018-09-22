<?php
/**
 * Cache>Memcache component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.3.2
 */

namespace Alonity\Components\Cache;

class MemcacheCacheException extends \Exception {}

class Memcache {

	private $local = [];

	private $options = [];

	private $memcache = null;

	public function __construct(){
		$this->options = [
			'host' => '127.0.0.1',
			'port' => 11211,
			'timeout' => 3,
			'expire' => 0
		];
	}

	public function setOptions($options){
		$this->options = array_merge($this->options, $options);
	}

	/**
	 * Шифрование ключа
	 *
	 * @param $key mixed
	 *
	 * @return string
	 */
	public function makeKey($key){
		return md5(var_export($key, true));
	}

	/**
	 * Взаимодействие с хранилищем Memcache
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return \Memcache
	 */
	private function getMemcache(){
		if(!is_null($this->memcache)){ return $this->memcache; }

		if(!class_exists('\Memcache')){
			throw new MemcacheCacheException("Memcache is not found");
		}

		$this->memcache = new \Memcache();

		$link = $this->memcache->connect($this->options['host'], $this->options['port'], $this->options['timeout']);

		if(!$link){
			throw new MemcacheCacheException("Connection error");
		}

		return $this->memcache;
	}

	/**
	 * Кэширует значение в хранилище Memcache
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function set($key, $value, $expire=null, $path=null){

		if(is_null($expire)){ $expire = $this->options['expire']; }

		if(is_null($path)){ $path = 'alonitycache:'; }

		$key = $path.$this->makeKey($key);

		if($this->getMemcache()->set($key, json_encode($value), $expire)===false){
			throw new MemcacheCacheException("Memcache set return false");
		}

		$this->local[$key] = $value;

		return $value;
	}

	/**
	 * Кэширует значения в хранилище Memcache, используя ассоциотивный массив
	 *
	 * @param $params array
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function setMultiple($params, $expire=null, $path=null){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		if(is_null($expire)){ $expire = $this->options['expire']; }

		if(is_null($path)){ $path = 'alonitycache:'; }

		$memcache = $this->getMemcache();

		foreach($params as $k => $v){

			$key = $path.$this->makeKey($k);

			if($memcache->set($key, json_encode($v), $expire)===false){
				throw new MemcacheCacheException("Memcache set return false");
			}

			$this->local[$key] = $v;

			$result[$k] = $v;
		}

		return $result;
	}

	/**
	 * Возвращает кэшируемое значение из хранилища Memcache
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function get($key, $path=null){

		if(is_null($path)){ $path = 'alonitycache:'; }

		$key = $path.$this->makeKey($key);

		if(isset($this->local[$key])){
			return $this->local[$key];
		}

		$get = $this->getMemcache()->get($key);

		if($get===false){ return null; }

		$result = json_decode($get, true);

		$this->local[$key] = $result;

		return $result;
	}

	/**
	 * Возвращает кэшируемые значения из хранилища Memcache, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return array
	 */
	public function getMultiple($keys, $path=null){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		if(is_null($path)){ $path = 'alonitycache:'; }

		$memcache = $this->getMemcache();

		foreach($keys as $key){

			$k = $path.$this->makeKey($key);

			if(isset($this->local[$k])){
				$result[$k] = $this->local[$k];
				continue;
			}

			$get = $memcache->get($k);

			if($get===false){
				throw new MemcacheCacheException("Memcache get return false");
			}

			$res = json_decode($get, true);

			$this->local[$k] = $res;

			$result[$k] = $res;

		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из хранилища Memcache
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @return boolean
	 */
	public function remove($key, $path=null){

		if(is_null($path)){ $path = 'alonitycache:'; }

		$key = $path.$this->makeKey($key);

		if(isset($this->local[$key])){
			unset($this->local[$key]);
		}

		if($this->getMemcache()->delete($key)===false){
			return false;
		}

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из хранилища Memcache, используя массив ключей
	 *
	 * @param $keys mixed
	 * @param $path string | null
	 *
	 * @return array
	 */
	public function removeMultiple($keys, $path=null){

		$memcache = $this->getMemcache();

		if(is_null($path)){ $path = 'alonitycache:'; }

		$result = [];

		foreach($keys as $key){
			$key = $path.$this->makeKey($key);

			if(isset($this->local[$key])){
				unset($this->local[$key]);
			}

			if($memcache->delete($key)===false){
				continue;
			}

			$result[] = $key;
		}

		return $result;
	}

	/**
	 * Очищает хранилище Memcache. Возвращает кол-во удаленных файлов
	 *
	 * @return integer
	 */
	public function clear(){

		return $this->getMemcache()->flush();
	}

	/**
	 * Возвращает экземпляр класса Memcache
	 *
	 * @return \Memcache
	 */
	public function getInstance(){
		return $this->getMemcache();
	}
}

?>