<?php
/**
 * Database MySQLi Delete component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.0
 */

namespace Alonity\Components\Database\MySQLi;

class MySQLiDeleteException extends \Exception {}

class Delete {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	/**
	 * @var \mysqli_result
	 */
	private $result = false;

	private $from = '';

	private $where = [];

	private $limit = 0;

	private $offset = 0;

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
	 * Имя таблицы, которая будет использоваться для удаления
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws MySQLiDeleteException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Delete()
	 */
	public function from($table){

		if(empty($table)){
			throw new MySQLiDeleteException('from must be not empty');
		}

		if(!is_string($table)){
			throw new MySQLiDeleteException('from must be a string');
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Ограничение кол-ва удаляемых строк
	 *
	 * @param $limit integer
	 *
	 * @return \Alonity\Components\Database\MySQLi\Delete()
	 */
	public function limit($limit){
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Выставляет смещение ограничения удаляемого результата
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return \Alonity\Components\Database\MySQLi\Delete()
	 */
	public function offset($offset){
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Условия
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example ["name=?", "`id`>='?'", "`id`<=3"],  returned name=?, "`id`>='?'", "`id`<=3"
	 *
	 * @throws MySQLiDeleteException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Delete()
	 */
	public function where($where, $values=[], $type=0x538){

		if(empty($where)){
			throw new MySQLiDeleteException("param where must be not empty");
		}

		if(!is_array($where)){
			throw new MySQLiDeleteException('param where must be array');
		}

		if(!is_array($values)){
			throw new MySQLiDeleteException("param values must be array");
		}

		if(!is_integer($type)){
			throw new MySQLiDeleteException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	private function filterFrom($from){
		if(empty($from)){
			throw new MySQLiDeleteException("from must be not empty");
		}

		if(!is_string($from)){
			throw new MySQLiDeleteException("from must be a string");
		}

		return "`$from`";
	}

	private function filterWhere($where){

		$result = "";

		if(empty($where)){ return ""; }

		if(!is_array($where)){
			throw new MySQLiDeleteException("where must be array");
		}

		foreach($where as $ar){
			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLiDeleteException("params where and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = $this->obj->escape_string($value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return empty($result) ? "" : "WHERE $result";
	}

	private function filterLimit($limit){

		$limit = intval($limit);

		if(empty($limit)){
			return "";
		}

		return "LIMIT $limit";
	}

	private function filterOffset($offset){

		$offset = intval($offset);

		if(empty($offset)){
			return "";
		}

		return "OFFSET $offset";
	}

	public function getError(){
		return $this->obj->error;
	}

	public function getDeletedNum(){
		return $this->obj->affected_rows;
	}

	public function getSQL(){

		if(!is_null($this->sql)){
			return $this->sql;
		}

		$this->sql = $this->compileSQL();

		return $this->sql;
	}

	private function compileSQL(){

		$from = $this->filterFrom($this->from);

		$where = $this->filterWhere($this->where);

		$limit = $this->filterLimit($this->limit);

		$offset = $this->filterOffset($this->offset);

		return "DELETE FROM $from $where $limit $offset";
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
			return false;
		}

		return true;
	}
}

?>