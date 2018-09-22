<?php
/**
 * Database PostgreSQL Insert component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components\Database\PostgreSQL;

class PostgreSQLInsertException extends \Exception {}

class Insert {

	private $sql = null;

	private $into = '';

	private $columns = [];

	private $values = [];

	private $insert_num = 0;

	private $last_id = 0;

	public function __construct($obj){
		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для вставки
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned "my_table"
	 *
	 * @throws PostgreSQLInsertException
	 *
	 * @return \Alonity\Components\Database\PostgreSQL\Insert()
	*/
	public function into($table){

		if(empty($table)){
			throw new PostgreSQLInsertException('into must be not empty');
		}

		if(!is_string($table)){
			throw new PostgreSQLInsertException('into must be a string');
		}

		$this->into = $table;

		return $this;
	}

	/**
	 * Поля для вставки
	 *
	 * @param $columns array
	 *
	 * @example ['name', 'description'] returned ("name", "description")
	 *
	 * @throws PostgreSQLInsertException
	 *
	 * @return \Alonity\Components\Database\PostgreSQL\Insert()
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new PostgreSQLInsertException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new PostgreSQLInsertException('columns must be array');
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
	 * @throws PostgreSQLInsertException
	 *
	 * @return \Alonity\Components\Database\PostgreSQL\Insert()
	 */
	public function values($values){

		if(empty($values)){
			throw new PostgreSQLInsertException('values must be not empty');
		}

		if(!is_array($values)){
			throw new PostgreSQLInsertException('values must be array');
		}

		$this->values = $values;

		return $this;
	}

	/*private function changeQuotes($value){
		return str_replace('`', '"', $value);
	}*/

	private function filterInto($table){

		if(empty($table)){
			throw new PostgreSQLInsertException("into must be not empty");
		}

		if(!is_string($table)){
			throw new PostgreSQLInsertException("into must be a string");
		}

		return "\"$table\"";
	}

	private function filterColumns($columns){
		if(empty($columns)){
			throw new PostgreSQLInsertException("columns must be not empty");
		}

		if(!is_array($columns)){
			throw new PostgreSQLInsertException("columns must be array");
		}

		$result = "";

		foreach($columns as $v){
			$column = @pg_escape_string($this->obj, $v);

			$result .= "\"$column\",";
		}

		$result = mb_substr($result, 0, -1, 'UTF-8');

		return "($result)";
	}

	private function filterValues($values){
		if(empty($values)){
			throw new PostgreSQLInsertException("values must be not empty");
		}

		if(!is_array($values)){
			throw new PostgreSQLInsertException("values must be array");
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
					throw new PostgreSQLInsertException("columns size not equal values size");
				}

				foreach($array as $value){
					$value = @pg_escape_string($this->obj, $value);

					$items .= "'$value',";
				}

				$this->insert_num++;

				$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
			}
		}else{
			$items = "";

			if($columns!=sizeof($values)){
				throw new PostgreSQLInsertException("columns size not equal values size");
			}

			foreach($values as $value){
				$value = @pg_escape_string($this->obj, $value);

				$items .= "'$value',";
			}

			$this->insert_num++;

			$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
		}

		return mb_substr($lines, 0, -1, 'UTF-8');
	}

	public function getError(){
		return pg_last_error($this->obj);
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute($last_id=null){

		$into = $this->filterInto($this->into);

		$columns = $this->filterColumns($this->columns);

		$values = $this->filterValues($this->values);

		$returning = (!is_null($last_id)) ? "RETURNING \"$last_id\"" : "";

		$this->sql = "INSERT INTO $into $columns VALUES $values $returning";

		$this->result = pg_query($this->obj, $this->sql);

		if(!$this->result){
			$this->insert_num = 0;
			return false;
		}

		if(!is_null($last_id)){
			$this->last_id = intval(pg_fetch_result($this->result, 0, 0));
		}

		return true;
	}

	public function getInsertNum(){
		return $this->insert_num;
	}

	public function getLastID(){
		return $this->last_id;
	}
}

?>