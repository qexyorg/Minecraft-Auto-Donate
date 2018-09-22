<?php
/**
 * Cache component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
*/

namespace Alonity\Components;

use CacheException;
use Alonity\Components\Cache\Memcache as Memcache;
use Alonity\Components\Cache\Redis as Redis;
use Alonity\Components\Cache\File as File;

require_once(__DIR__.'/CacheException.php');

class Cache {

	private static $options = [
		'storage' => 'file', // file | memcache | redis
		'redis' => [
			'host' => '127.0.0.1',
			'port' => 6379,
			'password' => '',
			'base' => 0,
			'timeout' => 3,
			'key' => 'alonitycache',
		],
		'memcache' => [
			'host' => '127.0.0.1',
			'port' => 11211,
			'timeout' => 3,
		],
		'file' => [
			'path' => '/Uploads/cache',
		],
	];

	private static $once = [];

	private static $redis = null;

	private static $memcache = null;

	private static $file = null;

	/**
	 * Шифрование ключа
	 *
	 * @param $key mixed
	 *
	 * @return string
	*/
	public static function makeKey($key){
		return md5(var_export($key, true));
	}

	/**
	 * Выставление настроек
	 *
	 * @param $params array
	 *
	 * @throws CacheException
	 *
	 * @return boolean
	 */
	public static function setOptions($params){
		if(!is_array($params) || empty($params)){
			throw new CacheException("Options is not set");
		}

		self::$options = array_merge(self::$options, $params);

		return true;
	}

	/**
	 * Применяет ключу значение, доступное в пределах запроса
	 *
	 * @param $key mixed
	 * @param $value mixed
	 *
	 * @return mixed
	*/
	public static function setOnce($key, $value){

		$key = self::makeKey($key);

		self::$once[$key] = $value;

		return $value;
	}

	/**
	 * Применяет ключам значения через ассоциативный, доступные в пределах запроса
	 *
	 * @param $params array
	 *
	 * @return array
	*/
	public static function setOnceMultiple($params){
		$result = [];

		if(!is_array($params) && empty($params)){
			return $result;
		}

		foreach($params as $k => $v){
			$k = self::makeKey($k);

			$result[$k] = $v;

			self::$once[$k] = $v;
		}

		return $result;
	}

	/**
	 * Возвращает значение по ключу, доступное в пределах одного запроса
	 *
	 * @param $key mixed
	 *
	 * @return mixed
	*/
	public static function getOnce($key){
		$key = self::makeKey($key);

		return (!isset(self::$once[$key])) ? null : self::$once[$key];
	}

	/**
	 * Возвращает значения по ключам массива, доступные в пределах запроса
	 *
	 * @param $keys array
	 *
	 * @return array
	*/
	public static function getOnceMultiple($keys){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		foreach($keys as $key){
			$key = self::makeKey($key);

			if(!isset(self::$once[$key])){ continue; }

			$result[$key] = self::$once[$key];
		}

		return $result;
	}

	/**
	 * Удаляет значение по ключу, доступное в пределах одного запроса
	 *
	 * @param $key
	 *
	 * @return boolean
	*/
	public static function removeOnce($key){
		$key = self::makeKey($key);

		if(!isset(self::$once[$key])){
			return false;
		}

		unset(self::$once[$key]);

		return true;
	}

	/**
	 * Удаляет значения по ключам массива, доступные в пределах запроса
	 *
	 * @param $keys array
	 *
	 * @return array
	*/
	public static function removeOnceMultiple($keys){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		foreach($keys as $key){
			$key = self::makeKey($key);

			if(!isset(self::$once[$key])){ continue; }

			$result[] = $key;

			unset(self::$once[$key]);
		}

		return $result;
	}

	/**
	 * Кэширует значение в хранилище, выставленное по умолчанию в опциях
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return mixed
	 */
	public static function set($key, $value, $expire=null, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->set($key, $value, $expire, $path); break;
			case 'redis': return self::getRedis()->set($key, $value, $expire, $path); break;
			case 'memcache': return self::getMemcache()->set($key, $value, $expire, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Кэширует значения в хранилище, выставленное по умолчанию в опциях, используя ассоциотивный массив
	 *
	 * @param $params array
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return array
	 */
	public static function setMultiple($params, $expire=null, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->setMultiple($params, $expire, $path); break;
			case 'redis': return self::getRedis()->setMultiple($params, $expire, $path); break;
			case 'memcache': return self::getMemcache()->setMultiple($params, $expire, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Возвращает значение из хранилища, выставленно по умолчанию в опциях
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return mixed
	 */
	public static function get($key, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->get($key, $path); break;
			case 'redis': return self::getRedis()->get($key, $path); break;
			case 'memcache': return self::getMemcache()->get($key, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Возвращает значения из хранилища, выставленно по умолчанию в опциях, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return array
	 */
	public static function getMultiple($keys, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->getMultiple($keys, $path); break;
			case 'redis': return self::getRedis()->getMultiple($keys, $path); break;
			case 'memcache': return self::getMemcache()->getMultiple($keys, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Удаляет значение по ключу
	 *
	 * @param $key string
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return boolean
	*/
	public static function remove($key, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->remove($key, $path); break;
			case 'redis': return self::getRedis()->remove($key, $path); break;
			case 'memcache': return self::getMemcache()->remove($key, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Удаляет значения из хранилища, выставленно по умолчанию в опциях, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string
	 *
	 * @throws CacheException
	 *
	 * @return array
	 */
	public static function removeMultiple($keys, $path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->removeMultiple($keys, $path); break;
			case 'redis': return self::getRedis()->removeMultiple($keys, $path); break;
			case 'memcache': return self::getMemcache()->removeMultiple($keys, $path); break;

		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Очищает хранилище, заданное через опции
	 *
	 * @param $path string | null
	 *
	 * @throws CacheException
	 *
	 * @return integer
	*/
	public static function clear($path=null){
		switch(self::$options['storage']){
			case 'file': return self::getFile()->clear($path); break;
			case 'redis': return self::getRedis()->clear($path); break;
			case 'memcache': return self::getMemcache()->clear($path); break;
		}

		throw new CacheException("Undefined cache storage");
	}

	/**
	 * Возвращает экземпляр класса Alonity\Components\Cache\Memcache
	 *
	 * @return Memcache()
	*/
	public static function getMemcache(){
		if(!is_null(self::$memcache)){ return self::$memcache; }

		if(!class_exists('Alonity\Components\Cache\Memcache')){
			require_once(__DIR__.'/Memcache.php');
		}

		self::$memcache = new Memcache();

		self::$memcache->setOptions(self::$options['memcache']);

		return self::$memcache;
	}

	/**
	 * Возвращает экземпляр класса Alonity\Components\Cache\Redis
	 *
	 * @return Redis()
	 */
	public static function getRedis(){
		if(!is_null(self::$redis)){ return self::$redis; }

		if(!class_exists('Alonity\Components\Cache\Redis')){
			require_once(__DIR__.'/Redis.php');
		}

		self::$redis = new Redis();

		self::$redis->setOptions(self::$options['redis']);

		return self::$redis;
	}

	/**
	 * Возвращает экземпляр класса Alonity\Components\Cache\File
	 *
	 * @return File()
	 */
	public static function getFile(){
		if(!is_null(self::$file)){ return self::$file; }

		if(!class_exists('Alonity\Components\Cache\File')){
			require_once(__DIR__.'/File.php');
		}

		self::$file = new File();

		self::$file->setOptions(self::$options['file']);

		return self::$file;
	}
}

?>