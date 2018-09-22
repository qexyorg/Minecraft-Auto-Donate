<?php
/**
 * Input filter component of Alonity Framework
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

class FilterInputException extends \Exception {}

class _Input {
	const TYPE_STRING = 0x00;

	const TYPE_INTEGER = 0x01;

	const TYPE_FLOAT = 0x02;

	const TYPE_BOOLEAN = 0x03;

	const TYPE_EMAIL = 0x04;

	const TYPE_URL = 0x05;

	const TYPE_SIMPLE_STRING = 0x06;

	const TYPE_DOMAIN = 0x07;

	const TYPE_ENTITIES = 0x08;

	// not complete - input for array values

	private $data = [];

	private $result = [];

	private $props = [];

	private $validation = '';

	public function __construct($data){
		if(!is_array($data)){
			throw new FilterInputException("input must be array");
		}

		$this->data = $data;
	}

	public function add($name, $type=0x00, $minlen=0, $maxlen=0, $required=false, $default=''){

		if(empty($name)){
			throw new FilterInputException("param name must be not empty");
		}

		if($type<0x00 || $type>0x08){
			throw new FilterInputException("unexpected type");
		}

		if(!is_int($maxlen)){
			throw new FilterInputException("param maxlen must be integer");
		}

		$this->props[] = [
			'name' => $name,
			'type' => $type,
			'minlength' => intval($minlen),
			'maxlength' => intval($maxlen),
			'required' => ($required===true) ? true : false,
			'default' => $default
		];

		return $this;
	}

	public function filter(){
		if(empty($this->props)){
			throw new FilterInputException("input must be not empty");
		}

		foreach($this->props as $k => $v){

			$name = $v['name'];

			$value = (!isset($this->data[$name])) ? $v['default'] : $this->data[$name];

			if(is_int($v['type'])){
				$value = $this->filterby($value, $v['type']);
			}else{
				$value = preg_replace('/[^'.$v['type'].']+/u', '', $value);
			}

			if($v['maxlength']>0 && mb_strlen($value, 'UTF-8')>$v['maxlength']){
				$value = mb_substr($value, 0, $v['maxlength'], 'UTF-8');
			}

			$this->result[$name] = $value;
		}

		return $this->result;
	}

	public function getValidationMessage(){
		return $this->validation;
	}

	public function isValid(){
		if(empty($this->props)){
			return false;
		}

		foreach($this->props as $k => $v){

			$name = $v['name'];

			if(!isset($this->data[$name]) && $v['required']){
				$this->validation = "index \"$name\" is required";
				return false; break;
			}

			if($v['required'] && $this->data[$name]==''){
				$this->validation = "index \"$name\" is required";
				return false; break;
			}

			$value = (isset($this->data[$name])) ? $this->data[$name] : $v['default'];

			if($value!='' || $v['required']){
				if($v['minlength']>0 && mb_strlen($value, 'UTF-8')<$v['minlength']){
					$this->validation = "minimal length of \"$name\" is {$v['minlength']}";
					return false; break;
				}

				if($v['maxlength']>0 && mb_strlen($value, 'UTF-8')>$v['maxlength']){
					$this->validation = "maximal length of \"$name\" is {$v['maxlength']}";
				}
			}
		}

		return true;
	}

	public function getResult(){
		return $this->result;
	}

	private function filterby($value, $type){
		switch($type){
			case self::TYPE_STRING: return strval($value); break;
			case self::TYPE_INTEGER: return intval($value); break;
			case self::TYPE_FLOAT: return floatval($value); break;
			case self::TYPE_BOOLEAN: return boolval($value); break;
			case self::TYPE_EMAIL: return preg_replace('/[^\w\.\-\@]+/i', '', $value); break;
			case self::TYPE_URL: return strip_tags(filter_var($value, FILTER_SANITIZE_URL)); break;
			case self::TYPE_SIMPLE_STRING: return preg_replace('/[^\w\.\-]+/i', '', $value); break;
			case self::TYPE_ENTITIES: return htmlspecialchars($value, ENT_QUOTES); break;
			case self::TYPE_DOMAIN: return preg_replace('/[^a-z0-9-\.]+/i', '', $value); break;
		}

		return $value;
	}
}

?>