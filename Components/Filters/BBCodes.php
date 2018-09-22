<?php
/**
 * BBCodes filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.3
 */

namespace Alonity\Components\Filters;

class FilterBBCodesException extends \Exception {}

class _BBCodes {

	private static function preg_replace_recursive($pattern, $replacement, $string){

		$string = preg_replace($pattern, $replacement, $string);

		return (!preg_match($pattern, $string)) ? $string : self::preg_replace_recursive($pattern, $replacement, $string);
	}

	private static function codeparse($string){
		return preg_replace_callback("/\[code\](.*)\[\/code\]/iUs", function($matches){
			return '<code>'.str_replace(['[', ']'], ['&#91;', '&#93;'], $matches[1]).'</code>';
		}, $string);
	}

	public static function parse($string, $specialchars=true){
		if($specialchars){
			$string = htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
		}

		$string = self::codeparse($string);

		foreach(self::getPatterns() as $pattern => $replace){

			$repl = (is_array($replace) && isset($replace[1])) ? $replace[0] : $replace;

			if(is_array($replace) && isset($replace[1]) && $replace[1]===false){
				$string = preg_replace("/$pattern/isU", $repl, $string);
			}else{
				$string = self::preg_replace_recursive("/$pattern/isU", $repl, $string);
			}
		}

		return nl2br($string);
	}

	private static function getPatterns(){
		return [
			'\[b\](.*)\[\/b\]' => '<b class="bb-bold">$1</b>',

			'\[i\](.*)\[\/i\]' => '<i class="bb-italic">$1</i>',

			'\[u\](.*)\[\/u\]' => '<u class="bb-underline">$1</u>',

			'\[s\](.*)\[\/s\]' => '<s class="bb-strike">$1</s>',

			'\[left\](.*)\[\/left\]' => '<div class="bb-text-left">$1</div>',

			'\[center\](.*)\[\/center\]' => '<div class="bb-text-center">$1</div>',

			'\[right\](.*)\[\/right\]' => '<div class="bb-text-right">$1</div>',

			'\[line\]' => ['<div class="bb-line"></div>', false],

			'\[youtube\]https\:\/\/www\.youtube\.com\/watch\?v=([\w\-]{5,15})\[\/youtube\]' => ['<iframe width="516" height="290" src="https://www.youtube.com/embed/$1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>', false],

			'\[spoiler\](.*)\[\/spoiler\]' => '<div class="bb-spoiler-wrapper"><div class="bb-spoiler"><a href="#" class="bb-spoiler-trigger">Спойлер</a><div class="bb-spoiler-text">$1</div></div></div>',

			'\[spoiler="([^"\>\<\n]+)"\](.*)\[\/spoiler\]' => '<div class="bb-spoiler-wrapper"><div class="bb-spoiler"><a href="#" class="bb-spoiler-trigger">$1</a><div class="bb-spoiler-text">$2</div></div></div>',

			'\[color="#([0-9a-f]{6})"\](.*)\[\/color\]' => '<span class="bb-color" style="color: #$1;">$2</span>',

			'\[size="([1-6])"\](.*)\[\/size\]' => '<h$1 class="bb-size-$1">$2</h$1>',

			'\[img\]((?:f|ht)(?:tp)s?\:\/\/[^\s]+)\[\/img\]' => ['<img class="bb-image" src="$1" alt="IMAGE" />', false],

			'\[quote\](.*)\[\/quote\]' => '<div class="bb-quote-wrapper"><div class="bb-quote"><div class="bb-quote-text">$1</div></div></div>',

			'\[quote="([^\n"\>\<]+)"\](.*)\[\/quote\]' => '<div class="bb-quote-wrapper"><div class="bb-quote"><div class="bb-quote-title">$1</div><div class="bb-quote-text">$2</div></div></div>',

			'\[url="((?:f|ht)(?:tp)s?\:\/\/[^\s"\n]+)"\](.*)\[\/url\]' => ['<a class="bb-url" href="$1">$2</a>', false],

			'\[url\]((?:f|ht)(?:tp)s?\:\/\/[^\s"]+)\[\/url\]' => ['<a class="bb-url" href="$1">$1</a>', false],
		];
	}
}

?>