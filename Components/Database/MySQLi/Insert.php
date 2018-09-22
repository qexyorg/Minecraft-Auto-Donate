<?php
/**
 * Database MySQLi Insert component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Database\MySQLi;

class MySQLiInsertException extends \Exception {}

class Insert {

	private $sql = null;

	private $into = '';

	/**
	 * @var \mysqli_result
	 */
	private $result = false;

	private $columns = [];

	private $values = [];

	private $insert_num = 0;

	/**
	 * @var \mysqli
	*/
	private $obj = null;

	public function __construct($obj){
		/**
		 * @return \mysqli
		*/
		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для вставки
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws MySQLiInsertException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Insert()
	*/
	public function into($table){

		if(empty($table)){
			throw new MySQLiInsertException('into must be not empty');
		}

		if(!is_string($table)){
			throw new MySQLiInsertException('into must be a string');
		}

		$this->into = $table;

		return $this;
	}

	/**
	 * Поля для вставки
	 *
	 * @param $columns array
	 *
	 * @example ['name', 'description'] returned (`name`, `description`)
	 *
	 * @throws MySQLiInsertException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Insert()
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new MySQLiInsertException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLiInsertException('columns must be array');
		}

		$this->columns = array_replace_recursive($this->columns, $columns);

		return $this;
	}

	/**
	 * Значения для вставки
	 *
	 * @param $values array
	 *
	 * @example ['Hello', 'World'] returned ('Hello', 'World')
	 * @example [['Hello', 'World'], ['Example', 'Field']] returned ('Hello', 'World'), ('Hello', 'World')
	 *
	 * @throws MySQLiInsertException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Insert()
	 */
	public function values($values){

		if(empty($values)){
			throw new MySQLiInsertException('values must be not empty');
		}

		if(!is_array($values)){
			throw new MySQLiInsertException('values must be array');
		}

		$this->values = $values;

		return $this;
	}

	private function filterInto($table){

		if(empty($table)){
			throw new MySQLiInsertException("into must be not empty");
		}

		if(!is_string($table)){
			throw new MySQLiInsertException("into must be a string");
		}

		return "`$table`";
	}

	private function filterColumns($columns){
		if(empty($columns)){
			throw new MySQLiInsertException("columns must be not empty");
		}

		if(!is_array($columns)){
			throw new MySQLiInsertException("columns must be array");
		}

		$result = "";

		foreach($columns as $v){
			$column = $this->obj->escape_string($v);

			$result .= "`$column`,";
		}

		$result = mb_substr($result, 0, -1, 'UTF-8');

		return "($result)";
	}

	private function filterValues($values){
		if(empty($values)){
			throw new MySQLiInsertException("values must be not empty");
		}

		if(!is_array($values)){
			throw new MySQLiInsertException("values must be array");
		}

		$assoc = false;

		foreach($values as $v){
			if(!is_array($v)){ continue; }

			$assoc = true;
		}

		$lines = "";

		$columns = sizeof($this->columns);

		if($assoc){

			foreach($values as $array){
				$items = "";

				if($columns!=sizeof($array)){
					throw new MySQLiInsertException("columns size not equal values size");
				}

				foreach($array as $value){
					$value = $this->obj->escape_string($value);

					$items .= "'$value',";
				}

				$this->insert_num++;

				$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
			}
		}else{
			$items = "";

			if($columns!=sizeof($values)){
				throw new MySQLiInsertException("columns size not equal values size");
			}

			foreach($values as $value){
				$value = $this->obj->escape_string($value);

				$items .= "'$value',";
			}

			$this->insert_num++;

			$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
		}

		return mb_substr($lines, 0, -1, 'UTF-8');
	}

	public function getError(){
		return $this->obj->error;
	}

	public function getSQL(){

		if(!is_null($this->sql)){
			return $this->sql;
		}

		$this->sql = $this->compileSQL();

		return $this->sql;
	}

	private function compileSQL(){

		$into = $this->filterInto($this->into);

		$columns = $this->filterColumns($this->columns);

		$values = $this->filterValues($this->values);

		return "INSERT INTO $into $columns VALUES $values";
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$sql = $this->getSQL();

		$this->result = $this->obj->query($sql);

		if($this->result===false){
			$this->insert_num = 0;
			return false;
		}

		return true;
	}

	public function getInsertNum(){
		return $this->insert_num;
	}

	public function getLastID(){
		return $this->obj->insert_id;
	}
}

?>