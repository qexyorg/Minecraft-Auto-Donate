<?php
/**
 * Crypt component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.1
 */

namespace Alonity\Components;

use CryptException;

require_once(__DIR__.'/CryptException.php');

class Crypt {

	/**
	 * Преобразует значение в хэш, используя алгоритмы PHP
	 * @link http://php.net/manual/ru/function.hash.php
	 *
	 * @param $value mixed
	 * @param $type string
	 *
	 * @return string
	 */
	public static function Hash($value, $type){
		return hash($type, $value);
	}

	/**
	 * Преобразует значение в MD5 хэш
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function MD5($value){
		return md5($value);
	}

	/**
	 * Преобразует значение в MD5 хэш с солью
	 *
	 * @param $value mixed
	 * @param $salt string
	 *
	 * @return string
	 */
	public static function saledMD5($value, $salt=''){
		return self::MD5($value.$salt);
	}

	/**
	 * Преобразует значение в SHA1 хэш
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function SHA1($value){
		return sha1($value);
	}

	/**
	 * Преобразует значение в SHA1 хэш с солью
	 *
	 * @param $value mixed
	 * @param $salt string
	 *
	 * @return string
	 */
	public static function saledSHA1($value, $salt){
		return self::SHA1(self::SHA1($value).self::SHA1($salt));
	}

	/**
	 * Преобразует значение в CRC32 хэш
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function CRC32($value){
		return crc32($value);
	}

	/**
	 * Преобразует значение в CRC32 хэш с солью
	 *
	 * @param $value mixed
	 * @param $salt string
	 *
	 * @return string
	*/
	public static function saledCRC32($value, $salt){
		return self::CRC32(self::CRC32($value).self::CRC32($salt));
	}

	/**
	 * Преобразует строку в массив
	 *
	 * @param $string string
	 *
	 * @return array
	 */
	private static function toArray($string){
		$len = mb_strlen($string, 'UTF-8');

		$result = [];

		for($i=0; $i<$len; $i++){
			$result[] = mb_substr($string, $i, 1, 'UTF-8');
		}

		return $result;
	}

	/**
	 * Возвращает случайную строку из латинских букв, цифр, кириллических букв, знаков
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return float
	 */
	public static function random($min=1, $max=10){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
		$chars .= '!~`@#$%^&*()_+=-?:;][/.,';
		$chars .= 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';

		$symbols = mb_strlen($chars, 'UTF-8')-1;

		$chars = self::toArray($chars);

		$string = '';

		$len = mt_rand($min, $max);

		for($i=0;$i<$len;$i++){
			$string .= $chars[mt_rand(0, $symbols)];
		}

		return $string;
	}

	/**
	 * Возвращает случайную строку из латинских букв и цифр
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return float
	 */
	public static function randomStringLatin($min=1, $max=10){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';

		$symbols = mb_strlen($chars, 'UTF-8')-1;

		$chars = self::toArray($chars);

		$string = '';

		for($i=0;$i<mt_rand($min, $max);$i++){
			$string .= $chars[mt_rand(0, $symbols)];
		}

		return $string;
	}

	/**
	 * Возвращает случайное целое число
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return integer
	 */
	public static function randomInt($min=1, $max=10){
		if(function_exists('random_int')){
			return random_int($min, $max);
		}

		return mt_rand($min, $max);
	}

	/**
	 * Возвращает случайное число с плавающей запятой
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return float
	 */
	public static function randomFloat($min=1, $max=10){
		if(function_exists('random_int')){
			$left = random_int($min, $max);
			$right = random_int($min, $max);
		}else{
			$left = mt_rand($min, $max);
			$right = mt_rand($min, $max);
		}

		return floatval($left.'.'.$right);
	}

	/**
	 * Возвращает случайное булевое значение true/false
	 *
	 * @return boolean
	*/
	public static function randomBoolean(){
		return (mt_rand(0, 1)==1) ? true : false;
	}

	/**
	 * Создает хэш пароля используя алгоритм Blowfish. Если установлен параметр strict, то будет использоваться алгоритм haval256,4
	 *
	 * @param $value string
	 * @param $strict boolean
	 *
	 * @throws CryptException
	 *
	 * @return string
	 */
	public static function createPassword($value, $strict=false){
		if(!$strict){
			return password_hash($value, PASSWORD_BCRYPT, [
				'cost' => 12
			]);
		}

		$salt = self::randomStringLatin(16, 32);

		$string = self::Hash(strval($value).$salt, 'haval256,4');

		return '$haval256$4$'.$salt.'$'.$string;
	}

	/**
	 * Проверяет пароль с хэшем
	 *
	 * @param $value string
	 * @param $hash string
	 * @param $strict boolean
	 *
	 * @throws CryptException
	 *
	 * @return boolean
	*/
	public static function checkPassword($value, $hash, $strict=false){

		if(!$strict){
			if(!function_exists('password_verify')){
				throw new CryptException("use strict mode for php < 5.5");
			}

			return password_verify($value, $hash);
		}

		$props = explode('$', $hash);

		if(sizeof($props)!=5){
			return false;
		}

		$algo = "$props[1],{$props[2]}";

		$salt = $props[3];

		$string = '$haval256$4$'.$salt.'$'.self::Hash(strval($value).$salt, $algo);

		return ($string===$hash) ? true : false;
	}
}

?>