<?php
/**
 * Permissions component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 *
 */

namespace Alonity\Components\Permissions;

class PermissionsException extends \Exception {}

class Permissions {

	private static $group = 0;

	private static $data = [];

	private static $move = null;

	/**
	 * Создает хэш из параметров
	 *
	 * @param $params mixed
	 *
	 * @return string
	*/
	private static function hashed($params){
		return md5(var_export($params, true));
	}

	/**
	 * Устанавливает текущую группу
	 *
	 * @param $name mixed
	 *
	 * @return void
	*/
	public static function setCurrentGroup($name){
		self::$group = $name;
	}

	/**
	 * Возвращает текущую группу
	 *
	 * @return mixed
	 */
	public static function getCurrentGroup(){
		return self::$group;
	}

	/**
	 * Возвращает значение по ключу [и группе]
	 *
	 * @param $key mixed
	 * @param $group mixed
	 *
	 * @return mixed
	*/
	public static function get($key, $group=null){
		if(is_null($group)){
			$group = self::$group;
		}

		$hashgroup = self::hashed($group);

		if(!isset(self::$data[$hashgroup])){
			return null;
		}

		if(!isset(self::$data[$hashgroup][$key])){
			return null;
		}

		return self::$data[$hashgroup][$key];
	}

	/**
	 * Устанавливает значение по ключу [и группе]
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $group mixed
	 *
	 * @return mixed
	*/
	public static function set($key, $value, $group=null){
		if(is_null($group)){
			$group = self::$group;
		}

		$hashgroup = self::hashed($group);

		if(!isset(self::$data[$hashgroup])){
			self::$data[$hashgroup] = [];
		}

		self::$data[$hashgroup][$key] = $value;

		return $value;
	}

	/**
	 * Проверяет наличие ключа в группе
	 *
	 * @param $key mixed
	 * @param $group mixed
	 *
	 * @return boolean
	 */
	public static function exists($key, $group=null){
		if(is_null($group)){
			$group = self::$group;
		}

		$hashgroup = self::hashed($group);

		if(!isset(self::$data[$hashgroup])){
			return false;
		}

		return (isset(self::$data[$hashgroup][$key]));
	}

	/**
	 * Проверяет наличие группы
	 *
	 * @param $name mixed
	 *
	 * @return boolean
	 */
	public static function groupExists($name){

		$hashgroup = self::hashed($name);

		return (isset(self::$data[$hashgroup]));
	}

	/**
	 * Возвращает все привилегии группы
	 *
	 * @param $group mixed
	 *
	 * @return array
	*/
	public static function getAll($group=null){
		if(is_null($group)){
			$group = self::$group;
		}

		$hashgroup = self::hashed($group);

		if(!self::groupExists($group)){
			return [];
		}

		return self::$data[$hashgroup];
	}

	/**
	 * Сверяет значение
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $group mixed
	 *
	 * @return boolean
	*/
	public static function equal($key, $value, $group=null){
		return (self::get($key, $group)===$value);
	}

	/**
	 * Устанавливает привилегии группе
	 *
	 * @param $permissions array
	 * @param $group mixed
	 *
	 * @return array
	*/
	public static function setPermissions($permissions, $group=null){
		if(is_null($group)){
			$group = self::$group;
		}

		$hashgroup = self::hashed($group);

		if(!isset(self::$data[$hashgroup])){
			self::$data[$hashgroup] = $permissions;
		}else{
			self::$data[$hashgroup] = array_replace_recursive(self::$data[$hashgroup], $permissions);
		}

		return $permissions;
	}

	/**
	 * Возвращает экземпляр класса Move
	 *
	 * @return Move
	*/
	public static function move(){
		if(!is_null(self::$move)){
			return self::$move;
		}

		self::$move = new Move();

		return self::$move;
	}
}

?>