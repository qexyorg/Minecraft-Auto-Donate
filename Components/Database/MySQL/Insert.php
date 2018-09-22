<?php
/**
 * Database MySQL Insert component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Database\MySQL;

class MySQLInsertException extends \Exception {}

class Insert {

	private $sql = null;

	private $into = '';

	private $result = false;

	private $columns = [];

	private $values = [];

	private $insert_num = 0;

	public function __construct($obj){
		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для вставки
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws MySQLInsertException
	 *
	 * @return \Alonity\Components\Database\MySQL\Insert()
	*/
	public function into($table){

		if(empty($table)){
			throw new MySQLInsertException('into must be not empty');
		}

		if(!is_string($table)){
			throw new MySQLInsertException('into must be a string');
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
	 * @throws MySQLInsertException
	 *
	 * @return \Alonity\Components\Database\MySQL\Insert()
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new MySQLInsertException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLInsertException('columns must be array');
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
	 * @throws MySQLInsertException
	 *
	 * @return \Alonity\Components\Database\MySQL\Insert()
	 */
	public function values($values){

		if(empty($values)){
			throw new MySQLInsertException('values must be not empty');
		}

		if(!is_array($values)){
			throw new MySQLInsertException('values must be array');
		}

		$this->values = $values;

		return $this;
	}

	private function filterInto($table){

		if(empty($table)){
			throw new MySQLInsertException("into must be not empty");
		}

		if(!is_string($table)){
			throw new MySQLInsertException("into must be a string");
		}

		return "`$table`";
	}

	private function filterColumns($columns){
		if(empty($columns)){
			throw new MySQLInsertException("columns must be not empty");
		}

		if(!is_array($columns)){
			throw new MySQLInsertException("columns must be array");
		}

		$result = "";

		foreach($columns as $v){
			$column = @mysql_escape_string($v);

			$result .= "`$column`,";
		}

		$result = mb_substr($result, 0, -1, 'UTF-8');

		return "($result)";
	}

	private function filterValues($values){
		if(empty($values)){
			throw new MySQLInsertException("values must be not empty");
		}

		if(!is_array($values)){
			throw new MySQLInsertException("values must be array");
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
					throw new MySQLInsertException("columns size not equal values size");
				}

				foreach($array as $value){
					$value = @mysql_escape_string($value);

					$items .= "'$value',";
				}

				$this->insert_num++;

				$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
			}
		}else{
			$items = "";

			if($columns!=sizeof($values)){
				throw new MySQLInsertException("columns size not equal values size");
			}

			foreach($values as $value){
				$value = @mysql_escape_string($value);

				$items .= "'$value',";
			}

			$this->insert_num++;

			$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
		}

		return mb_substr($lines, 0, -1, 'UTF-8');
	}

	public function getError(){
		return mysql_error($this->obj);
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

		$this->result = mysql_query($sql, $this->obj);

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
		return mysql_insert_id($this->obj);
	}
}

?>