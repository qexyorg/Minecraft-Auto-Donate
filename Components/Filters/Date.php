<?php
/**
 * Date filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Filters;

class FilterDateException extends \Exception {}

class _Date {

	/**
	 * Преобразует секунды в кол-во оставшихся лет/месяцев/недель/дней/часов/минут/секунд
	 *
	 * @param $time integer
	 * @param $now integer
	 *
	 * @return array
	*/
	public static function expire($time, $now=null){
		$time = intval($time);

		$now = (is_null($now)) ? time() : intval($now);

		$seconds = $time - $now;

		$minute = 60;
		$hour = $minute * 60;
		$day = $hour * 24;
		$week = $day * 7;
		$month = $day * 30;
		$year = $day * 365;

		$years = intval($seconds / $year);
		$years_e = $seconds % $year;

		$months = intval($years_e / $month);
		$months_e = $years_e % $month;

		$weeks = intval($months_e / $week);

		$days = intval($months_e / $day);
		$days_e = $months_e % $day;

		$hours = intval($days_e / $hour);
		$hours_e = $days_e % $hour;

		$minutes = intval($hours_e / $minute);
		$minutes_e = $hours_e % $minute;

		return [
			'years' => $years,
			'months' => $months,
			'weeks' => $weeks,
			'days' => $days,
			'hours' => $hours,
			'minutes' => $minutes,
			'seconds' => $minutes_e
		];
	}

	/**
	 * Приводит дату к виду остатка от нужной даты
	 *
	 * @param $time integer
	 * @param $now integer
	 *
	 * @return string
	*/
	public static function toFormatExpire($time, $now=null){
		if(is_null($now)){ $now = time(); }

		$time = intval($time);
		$now = intval($now);

		$seconds = $time - $now;

		if($seconds<=0){ return ''; }

		$delcs = [
			'seconds' => ['секунд', 'секунды', 'секунда'],
			'minutes' => ['минут', 'минуты', 'минута'],
			'hours' => ['часов', 'часа', 'час'],
			'days' => ['дней', 'дня', 'день'],
			'months' => ['месяцев', 'месяца', 'месяц'],
			'years' => ['лет', 'года', 'год'],
		];

		if($seconds<60){
			return "{$seconds} ".self::decl($seconds, $delcs['seconds']);
		}

		$expire = self::expire($time, $now);

		$result = '';

		if($expire['years']>0){
			$result .= " {$expire['years']} ".self::decl($expire['years'], $delcs['years']);
		}

		if($expire['months']>0){
			$result .= " {$expire['months']} ".self::decl($expire['months'], $delcs['months']);
		}

		if($expire['days']>0){
			$result .= " {$expire['days']} ".self::decl($expire['days'], $delcs['days']);
		}

		if($expire['hours']>0){
			$result .= " {$expire['hours']} ".self::decl($expire['hours'], $delcs['hours']);
		}

		if($expire['minutes']>0){
			$result .= " {$expire['minutes']} ".self::decl($expire['minutes'], $delcs['minutes']);
		}

		if($expire['seconds']>0){
			$result .= " {$expire['seconds']} ".self::decl($expire['seconds'], $delcs['seconds']);
		}

		return $result;
	}

	/**
	 * Переводит число в указанный падеж
	 *
	 * @param $time integer
	 * @param $array array
	 *
	 * @return string
	*/
	public static function decl($time, $array){
		$end = $time % 10;

		if(sizeof($array)!=3){ return ''; }

		if($time>5 && $time<20){
			return @$array[0];
		}elseif($end > 1 && $end<5){
			return @$array[1];
		}elseif($end==1){
			return @$array[2];
		}

		return @$array[0];
	}

	/**
	 * Возвращает строку в правильном падеже в зависимости от числа
	 *
	 * @param $number integer
	 * @param $n1 string
	 * @param $n2 string
	 * @param $other string
	 *
	 * @return string
	*/
	public static function toCase($number, $n1='', $n2='', $other=''){
		$number = intval($number);

		if($number>20){ $number = $number % 10; }

		if($number==1){
			return $n1;
		}elseif($number==2 || $number==3 || $number==4){
			return $n2;
		}

		return $other;
	}

	private static function toFormatBefore($time, $default){
		$now = time();

		$seconds = $now-$time;

		if($seconds<5){
			return 'только что';
		}

		if($seconds<60){
			return $seconds.' '.self::toCase($seconds, 'секунду', 'секунды', 'секунд').' назад';
		}

		$minutes = intval($seconds / 60);

		if($seconds<3600){
			return $minutes.' '.self::toCase($minutes, 'минуту', 'минуты', 'минут').' назад';
		}

		if(date("dmY")===date("dmY", $time)){
			return 'сегодня в '.date("H:i", $time);
		}elseif(date('dmY', strtotime("-1 days"))===date("dmY", $time)){
			return 'вчера в '.date("H:i", $time);
		}else{
			return self::writeOut($time, $default);
		}
	}

	private static function toFormatAfter($time, $default){
		$now = time();

		$seconds = $now-$time;

		if($seconds>-5){
			return 'сейчас';
		}

		if($seconds>-60){
			return 'через '.$seconds.' '.self::toCase($seconds, 'секунду', 'секунды', 'секунд');
		}

		$minutes = intval($seconds / 60)*-1;

		if($seconds>-3600){
			return 'через '.$minutes.' '.self::toCase($minutes, 'минуту', 'минуты', 'минут');
		}

		if(date("dmY")===date("dmY", $time)){
			return 'сегодня в '.date("H:i", $time);
		}elseif(date('dmY', strtotime("+1 days"))===date("dmY", $time)){
			return 'завтра в '.date("H:i", $time);
		}else{
			return self::writeOut($time, $default);
		}
	}

	/**
	 * Приводит дату к пользовательскому виду
	 *
	 * @param $time integer | null
	 * @param $default string
	 *
	 * @throws FilterDateException
	 *
	 * @return string
	*/
	public static function toFormat($time=null, $default="d F Y в H:i"){
		$now = time();

		if(!is_string($default)){
			throw new FilterDateException('param default must be a string');
		}

		if(is_null($time)){ $time = $now; }

		$time = intval($time);

		return (($now-$time)>=0) ? self::toFormatBefore($time, $default) : self::toFormatAfter($time, $default);
	}

	/**
	 * Возвращает дату в нужном формате с переводом названий месяцев
	 *
	 * @param $time integer
	 * @param $format string
	 *
	 * @return string
	*/
	public static function writeOut($time=0, $format="d F Y в H:i"){
		$time = intval($time);
		$date = date($format, $time);

		$search = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$replace = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

		$date = str_ireplace($search, $replace, $date);

		return $date;
	}
}

?>