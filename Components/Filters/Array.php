<?php
/**
 * Array filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components\Filters;

class FilterArrayException extends \Exception {}

class _Array {
	const KEYS_FIRST = 0x00;
	const VALUES_FIRST = 0x01;

	/**
	 * Преобразует ассоциотивный массив в строку
	 *
	 * @param $array array
	 * @param $separator1 mixed
	 * @param $separator2 mixed
	 * @param $type integer
	 *
	 * @throws FilterStringException
	 *
	 * @return string
	 */
	public static function implodeAssoc($array, $separator1='', $separator2='', $type=0x00){
		if(self::KEYS_FIRST!==$type && self::VALUES_FIRST!==$type){
			throw new FilterStringException("Unexpected type");
		}

		if(!is_array($array)){
			throw new FilterStringException("first param must be array");
		}

		$result = '';

		foreach($array as $k => $v){
			if(!is_int($k)){
				$result .= (self::KEYS_FIRST===$type) ? $k.$separator2.$v : $v.$separator2.$k;
				$result .= $separator1;
			}
		}

		$len = mb_strlen($separator1, 'UTF-8')*-1;

		$result = mb_substr($result, 0, $len, 'UTF-8');

		return $result;
	}

	/**
	 * Рекурсивная проверка наличия ключа в массиве
	 *
	 * @param $key mixed
	 * @param $array array | boolean
	 *
	 * @return boolean
	*/
	public static function keyExistsRecursive($key, $array){
		if(is_bool($array)){
			return $array;
		}

		$result = false;

		foreach($array as $k => $v){
			if($k===$key){
				$result = true;
				break;
			}

			if(is_array($v)){
				$result = self::keyExistsRecursive($key, $v);
			}

			if($result===true){
				break;
			}
		}

		return $result;
	}

	/**
	 * Проверка наличия ключа в массиве
	 *
	 * @param $key mixed
	 * @param $array array
	 *
	 * @return boolean
	 */
	public static function keyExists($key, $array){
		return array_key_exists($key, $array);
	}

	/**
	 * Рекурсивная проверка наличия значения в массиве
	 *
	 * @param $value mixed
	 * @param $array array | boolean
	 *
	 * @return boolean
	 */
	public static function valueExistsRecursive($value, $array){
		if(is_bool($array)){
			return $array;
		}

		$result = false;

		foreach($array as $v){
			if($v===$value){
				$result = true;
				break;
			}

			if(is_array($v)){
				$result = self::valueExistsRecursive($value, $v);
			}

			if($result===true){
				break;
			}
		}

		return $result;
	}

	/**
	 * Проверка наличия значения в массиве
	 *
	 * @param $value mixed
	 * @param $array array
	 *
	 * @return boolean
	 */
	public static function valueExists($value, $array){
		return in_array($value, $array);
	}
}

?>