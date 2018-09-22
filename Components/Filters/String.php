<?php
/**
 * String filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.2
 */

namespace Alonity\Components\Filters;

class FilterStringException extends \Exception {}

class _String {

	private static $symbols = [
		'russian' => ['а'=>'a','А'=>'A',
			'б'=>'b','Б'=>'B',
			'в'=>'v','В'=>'V',
			'г'=>'g','Г'=>'G',
			'д'=>'d','Д'=>'D',
			'е'=>'e','Е'=>'E',
			'ж'=>'zh','Ж'=>'ZH',
			'з'=>'z','З'=>'Z',
			'и'=>'i','И'=>'I',
			'й'=>'y','Й'=>'Y',
			'к'=>'k','К'=>'K',
			'л'=>'l','Л'=>'L',
			'м'=>'m','М'=>'M',
			'н'=>'n','Н'=>'N',
			'о'=>'o','О'=>'O',
			'п'=>'p','П'=>'P',
			'р'=>'r','Р'=>'R',
			'с'=>'s','С'=>'S',
			'т'=>'t','Т'=>'T',
			'у'=>'u','У'=>'U',
			'ф'=>'f','Ф'=>'F',
			'х'=>'h','Х'=>'H',
			'ц'=>'c','Ц'=>'TS',
			'ч'=>'ch','Ч'=>'CH',
			'ш'=>'sh','Ш'=>'SH',
			'щ'=>'sch','Щ'=>'SHC',
			'ъ'=>'','Ъ'=>'',
			'ы'=>'i','Ы'=>'I',
			'ь'=>'','Ь'=>'',
			'э'=>'e','Э'=>'E',
			'ю'=>'yu','Ю'=>'YU',
			'я'=>'ya','Я'=>'YA'],

		'ukrainian' => [
			'і'=>'i','І'=>'I',
			'ї'=>'yi','Ї'=>'YI',
			'є'=>'e','Є'=>'E'

		]
	];

	/**
	 * Возвращает длину строки с учетом кодировки
	 *
	 * @param $string string
	 *
	 * @return integer
	*/
	public static function length($string){
		return mb_strlen($string, 'UTF-8');
	}

	/**
	 * Возвращает позицию подстроки с учетом кодировки
	 *
	 * @param $string string
	 * @param $needle string
	 *
	 * @return integer
	 */
	public static function pos($string, $needle){
		return mb_strpos($string, $needle, 0, 'UTF-8');
	}

	/**
	 * Добавляет набор символов в массив языков для преобразования строки в латиницу
	 *
	 * @param $name string
	 * @param $symbols array
	 *
	 * @throws FilterStringException
	 *
	 * @return void
	*/
	public static function pushLang($name, $symbols=[]){

		if(!is_string($name)){
			throw new FilterStringException("param name must be a string");
		}

		if(!is_array($symbols)){
			throw new FilterStringException("param symbols must be array");
		}

		self::$symbols[$name] = $symbols;
	}

	/**
	 * Преобразует символы в строке в латиницу
	 *
	 * @param $string string
	 * @param $except_to string
	 *
	 * @return string
	*/
	public static function toLatin($string, $except_to='-'){

		foreach(self::$symbols as $lang => $symbol){
			$string = strtr($string, $symbol);
		}

		return preg_replace('/[^a-zA-Z0-9-]/iu', $except_to, $string);
	}

	/**
	 * Удаляет HTML разметку из текста
	 *
	 * @param $string string
	 *
	 * @return string
	*/
	public static function removeTags($string){
		return strip_tags($string);
	}

	/**
	 * Пеобразует спецсимволы в строке в HTML сущности
	 *
	 * @param $string string
	 *
	 * @return string
	*/
	public static function toEntities($string){
		return htmlspecialchars($string, ENT_QUOTES);
	}

	/**
	 * Преобразует строку в массив
	 *
	 * @param $string string
	 *
	 * @return array
	*/
	public static function toArray($string){
		$len = mb_strlen($string, 'UTF-8');

		$result = [];

		for($i=0; $i<$len; $i++){
			$result[] = mb_substr($string, $i, 1, 'UTF-8');
		}

		return $result;
	}

	/**
	 * Приводит строку к верхнему регистру с учетом кодировки
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function toUpper($string){

		return mb_strtoupper($string, 'UTF-8');
	}

	/**
	 * Приводит строку к нижнему регистру с учетом кодировки
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function toLover($string){

		return mb_strtolower($string, 'UTF-8');
	}

	/**
	 * Вставляет строку после подстроки
	 *
	 * @param $input string
	 * @param $after mixed
	 * @param $string string
	 *
	 * @return string
	 */
	public static function after($input, $after, $string){
		$len = self::length($after);

		$pos = self::pos($input, $after);

		if($pos===false){
			return $input;
		}

		$leftpos = $pos+$len;

		$left = mb_substr($input, 0, $leftpos, 'UTF-8');

		$right = mb_substr($input, $leftpos, null, 'UTF-8');

		return $left.$string.$right;
	}

	/**
	 * Вставляет строку до подстроки
	 *
	 * @param $input string
	 * @param $before mixed
	 * @param $string string
	 *
	 * @return string
	 */
	public static function before($input, $before, $string){

		$pos = self::pos($input, $before);

		if($pos===false){
			return $input;
		}

		$left = mb_substr($input, 0, $pos, 'UTF-8');

		$right = mb_substr($input, $pos, null, 'UTF-8');

		return $left.$string.$right;
	}

	/**
	 * Вставляет подстроку в позицию в строке
	 *
	 * @param $input string
	 * @param $string string
	 * @param $pos integer
	 *
	 * @return string
	*/
	public static function push($input, $string, $pos=0){

		if($pos<=0){
			return $string.$input;
		}

		if($pos>=self::length($input)){
			return $input.$string;
		}

		$left = mb_substr($input, 0, $pos, 'UTF-8');
		$right = mb_substr($input, $pos, null, 'UTF-8');

		return $left.$string.$right;
	}

	/**
	 * Фильтрует E-Mail адрес, удаляя все символы из строки, кроме a-zA-Z0-9_-.@
	 *
	 * @param $string string
	 *
	 * @return string
	*/
	public static function email($string){
		$string = strval($string);

		return preg_replace('/[^\w\.\-\@]+/i', '', $string);
	}

	/**
	 * Проверяет, является ли строка E-Mail адресом
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function isEmail($string){

		return (preg_match('/^[\w]+([\w\-\.]+)?\@[a-z0-9]+([a-z0-9\-\.]+)?$/i', $string)) ? true : false;
	}

	/**
	 * Фильтрует E-Mail адрес, удаляя все символы из строки, кроме a-zA-Z0-9$-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=
	 * Так же к данному фильтру применяется функция удаления HTML тегов из строки
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function url($string){
		return self::removeTags(filter_var($string, FILTER_SANITIZE_URL));
	}

	/**
	 * Проверяет, является ли строка URL адресом
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function isUrl($string){
		return (filter_var($string, FILTER_VALIDATE_URL)===false) ? false : true;
	}
}

?>