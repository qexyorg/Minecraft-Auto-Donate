<?php
/**
 * Cache>Redis component of Alonity Framework
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

class RedisCacheException extends \Exception {}

class Redis {

	private $local = [];

	private $options = [];

	private $redis = null;

	public function __construct(){
		$this->options = [
			'host' => '127.0.0.1',
			'port' => 6379,
			'password' => '',
			'base' => 0,
			'timeout' => 3,
			'key' => 'alonitycache',
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
	 * Взаимодействие с хранилищем Redis
	 *
	 * @throws RedisCacheException
	 * @throws \RedisException
	 *
	 * @return \Redis
	 */
	private function getRedis(){
		if(!is_null($this->redis)){ return $this->redis; }

		if(!class_exists('\Redis')){
			throw new RedisCacheException("Redis is not found");
		}

		$this->redis = new \Redis();

		$link = $this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);

		if(!$link){
			throw new \RedisException("Connection error");
		}

		if(!$this->redis->auth($this->options['password'])){
			throw new \RedisException("Incorrect auth");
		}

		if(!$this->redis->select($this->options['base'])){
			throw new \RedisException("Selection error");
		}

		return $this->redis;
	}

	/**
	 * Возвращает кэшируемое значение из хранилища Redis
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @throws RedisCacheException
	 *
	 * @return mixed
	 */
	public function get($key, $path=null){

		if(is_null($path)){ $path = $this->options['key']; }

		$key = $this->makeKey($key);

		if(isset($this->local[$path.$key])){
			return $this->local[$path.$key];
		}

		$get = $this->getRedis()->hGet($path, $key);

		if($get===false){
			return null;
		}

		$result = json_decode($get, true);

		$this->local[$path.$key] = $result;

		return $result;
	}

	/**
	 * Возвращает кэшируемые значения из хранилища Redis, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @throws RedisCacheException
	 *
	 * @return array
	 */
	public function getMultiple($keys, $path=null){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		if(is_null($path)){ $path = $this->options['key']; }

		$redis = $this->getRedis();

		foreach($keys as $key){

			$key = $this->makeKey($key);

			if(isset($this->local[$path.$key])){
				$result[$path.$key] = $this->local[$path.$key];
				continue;
			}

			$get = $redis->hGet($path, $key);

			if($get===false){
				continue;
			}

			$res = json_decode($get, true);

			$this->local[$path.$key] = $res;

			$result[$path.$key] = $res;
		}

		return $result;
	}

	/**
	 * Кэширует значение в хранилище Redis
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @throws \RedisException
	 *
	 * @return mixed
	 */
	public function set($key, $value, $expire=null, $path=null){

		if(is_null($expire)){ $expire = $this->options['expire']; }

		if(is_null($path)){ $path = $this->options['key']; }

		$key = self::makeKey($key);

		if($this->getRedis()->hSet($path, $key, json_encode($value))===false){
			throw new \RedisException("Redis method hSet return false");
		}

		if($expire>0){ $this->getRedis()->setTimeout($path, $expire); }

		$this->local[$path.$key] = $value;

		return $value;
	}

	/**
	 * Кэширует значения в хранилище Redis, используя ассоциотивный массив
	 *
	 * @param $params array
	 * @param $expire integer | null
	 * @param $path integer | null
	 *
	 * @throws \RedisException
	 *
	 * @return array
	 */
	public function setMultiple($params, $expire=null, $path=null){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		if(is_null($expire)){ $expire = $this->options['expire']; }

		if(is_null($path)){ $path = $this->options['key']; }

		foreach($params as $k => $v){

			$key = self::makeKey($k);

			if($this->getRedis()->hSet($path, $key, json_encode($v))===false){
				throw new \RedisException("Redis method hSet return false");
			}

			if($expire>0){ $this->getRedis()->setTimeout($path, $expire); }

			$result[$path.$key] = $v;

			$this->local[$path.$key] = $v;
		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из хранилища Redis
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @return boolean
	 */
	public function remove($key, $path=null){

		$key = $this->makeKey($key);

		if(is_null($path)){ $path = $this->options['key']; }

		if(isset($this->local[$path.$key])){
			unset($this->local[$path.$key]);
		}

		if($this->getRedis()->hDel($path, $key)===false){
			return false;
		}

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из хранилища Redis, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @return array
	 */
	public function removeMultiple($keys, $path=null){

		$redis = $this->getRedis();

		if(is_null($path)){ $path = $this->options['key']; }

		$result = [];

		foreach($keys as $key){

			$key = $this->makeKey($key);

			if(isset($this->local[$path.$key])){
				unset($this->local[$path.$key]);
			}

			if($redis->hDel($path, $key)===false){
				continue;
			}

			$result[] = $key;
		}

		return $result;
	}

	/**
	 * Очищает хранилище Redis. Возвращает кол-во удаленных ключей
	 *
	 * @param $path string | null
	 *
	 * @return integer
	 */
	public function clear($path=null){

		if(is_null($path)){ $path = $this->options['key']; }

		$pathlen = mb_strlen($path, 'UTF-8');

		$delete = $this->getRedis()->del($path);

		foreach($this->local as $k => $v){
			if(mb_substr($k, 0, ($pathlen-1), 'UTF-8')==$path){
				unset($this->local[$k]);
			}
		}

		return ($delete===false) ? 0 : intval($delete);
	}

	/**
	 * Возвращает экземпляр класса Redis
	 *
	 * @return \Redis
	*/
	public function getInstance(){
		return $this->getRedis();
	}
}

?>