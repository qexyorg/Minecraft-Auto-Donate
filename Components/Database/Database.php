<?php
/**
 * Database component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.3.0
 */

namespace Alonity\Components;

use DatabaseException;

require_once(__DIR__.'/DatabaseException.php');

class Database {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	private static $queries = 0;

	private static $options = [
		'engine' => 'mysqli',
		'mysqli' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => 'Alonity\Components\Database\MySQLi',
			'dir' => '/MySQL/MySQLi.php',
			'key' => 0
		],
		'mysql' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => 'Alonity\Components\Database\MySQL',
			'dir' => '/MySQL/MySQL.php',
			'key' => 0
		],
		'postgres' => [
			'host' => '127.0.0.1',
			'port' => 5432,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'postgres',
			'password' => '',
			'class' => 'Alonity\Components\Database\PostgreSQL',
			'dir' => '/PostgreSQL/PostgreSQL.php',
			'key' => 0
		],
		'redis' => [
			'host' => '127.0.0.1',
			'port' => 6379,
			'timeout' => 3,
			'database' => 0,
			'password' => '',
			'class' => 'Alonity\Components\Database\Redis',
			'dir' => '/Redis/Redis.php',
			'key' => 0
		],
		'memcache' => [
			'host' => '127.0.0.1',
			'port' => 11211,
			'timeout' => 3,
			'class' => 'Alonity\Components\Database\Memcache',
			'dir' => '/Memcache/Memcache.php',
			'key' => 0
		]
	];

	private static $connections = [];

	private static $objects = [];

	private static $last_error = null;

	/**
	 * Выставление настроек
	 *
	 * @param $params array
	 *
	 * @throws DatabaseException
	 *
	 * @return boolean
	 */
	public static function setOptions($params){
		if(!is_array($params) || empty($params)){
			throw new DatabaseException("Options is not set");
		}

		self::$options = array_replace_recursive(self::$options, $params);

		return true;
	}

	/**
	 * Создание подключение к базе данных. Если подключение уже существует, возвращает его экземпляр.
	 *
	 * @throws DatabaseException
	 *
	 * @return object
	*/
	public static function connect(){

		$engine = self::$options['engine'];

		if(!isset(self::$options[$engine])){
			self::$last_error = "Unexpected default engine options";
			throw new DatabaseException(self::$last_error);
		}

		$options = self::$options[$engine];

		$token = md5(var_export($options['key'], true));

		if(isset(self::$connections[$token])){ return self::$connections[$token]; }

		$classname = $options['class'];

		if(!class_exists($classname)){
			require_once(__DIR__.$options['dir']);
		}

		if(!isset(self::$objects[$engine])){
			self::$objects[$engine] = new $classname();
		}

		$object = self::$objects[$engine];

		self::$connections[$token] = $object->connect($options);

		return self::$connections[$token];
	}

	public static function disconnect($engine=null, $key=0){

		if(is_null($engine)){
			$engine = self::$options['engine'];
		}

		if(!isset(self::$objects[$engine])){
			return false;
		}

		if(!self::$objects[$engine]->disconnect($key)){
			return false;
		}

		unset(self::$objects[$engine]);

		return true;
	}

	public static function getLastError(){
		return self::$last_error;
	}

	private static function getEngine(){
		if(!isset(self::$objects[self::$options['engine']])){
			self::connect();
		}

		return self::$objects[self::$options['engine']];
	}

	/**
	 * Возвращает кол-во запросов
	 *
	 * @return integer
	*/
	public static function getQueriesNum(){
		return self::$queries;
	}

	/**
	 * @return \Alonity\Components\Database\MySQLi\Select
	 * @return \Alonity\Components\Database\MySQL\Select
	 * @return \Alonity\Components\Database\PostgreSQL\Select
	*/
	public static function select(){
		self::$queries++;
		return self::getEngine()->select();
	}

	/**
	 * @return \Alonity\Components\Database\MySQLi\Insert
	 * @return \Alonity\Components\Database\MySQL\Insert
	 * @return \Alonity\Components\Database\PostgreSQL\Insert
	 */
	public static function insert(){
		self::$queries++;
		return self::getEngine()->insert();
	}

	/**
	 * @return \Alonity\Components\Database\MySQLi\Update
	 * @return \Alonity\Components\Database\MySQL\Update
	 * @return \Alonity\Components\Database\PostgreSQL\Update
	 */
	public static function update(){
		self::$queries++;
		return self::getEngine()->update();
	}

	/**
	 * @return \Alonity\Components\Database\MySQLi\Delete
	 * @return \Alonity\Components\Database\MySQL\Delete
	 * @return \Alonity\Components\Database\PostgreSQL\Delete
	 */
	public static function delete(){
		self::$queries++;
		return self::getEngine()->delete();
	}

	/**
	 * @param $sql string
	 *
	 * @return \mysqli_result
	 * @return resource
	 */
	public static function query($sql){
		self::$queries++;
		return self::getEngine()->query($sql);
	}

	/**
	 * @param $string string
	 *
	 * @return string
	 */
	public static function safeSQL($string){
		return self::getEngine()->safeSQL($string);
	}
}

?>