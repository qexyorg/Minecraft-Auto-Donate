<?php
/**
 * Database MySQL Select component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.4.1
 */

namespace Alonity\Components\Database\MySQL;

class MySQLSelectException extends \Exception {}

class Select {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	private $from = [];

	private $where = [];

	private $limit = 0;

	private $offset = 0;

	private $order = null;

	private $obj = null;

	private $columns = [];

	private $group = [];

	private $having = null;

	private $result = false;

	private $join = [];

	private $jointypes = [];

	public function __construct($obj){
		$this->jointypes = ['inner', 'left', 'right'];

		$this->obj = $obj;
	}

	/**
	 * Выставляет таблицу из которой будет извлечен результат
	 *
	 * @param $table array | string
	 *
	 * @example 'my_table' returned `my_table`
	 * @example array('t' => 'my_table') returned `t`.`my_table`
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	*/
	public function from($table=[]){

		if(empty($table)){
			throw new MySQLSelectException('from must be not empty');
		}

		if(!is_array($table) && !is_string($table)){
			throw new MySQLSelectException('from must be array or string');
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Выставляет условия AND по которым будет извлечен результат
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example array("`t`.`id`='?'" => 3, "`t`.`name`='?'" => 'hello') returned AND `t`.`id`='3' AND `t`.`name`='hello'
	 * @example array("`t`.`name`='?-?'" => 'hello') returned AND `t`.`name`='hello-hello'
	 * @example array("`t`.`name`='?-?'" => ['hello', 'world']) returned AND `t`.`name`='hello-world'
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function where($where, $values=[], $type=0x538){

		if(!is_array($where)){
			throw new MySQLSelectException('param where must be array');
		}

		if(!is_array($values)){
			throw new MySQLSelectException("param values must be array");
		}

		if(!is_integer($type)){
			throw new MySQLSelectException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	/**
	 * Выставляет условия группировки
	 *
	 * @param $value string
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function having($value){
		if(empty($value)){
			throw new MySQLSelectException('having must be not empty');
		}

		if(!is_string($value)){
			throw new MySQLSelectException('from must be a string');
		}

		$this->having = $value;

		return $this;
	}

	/**
	 * Выставляет ограничения возвращаемых результатов
	 *
	 * @param $limit integer
	 *
	 * @example 10 returned LIMIT 10
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function limit($limit){
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Выставляет смещение ограничения возвращаемого результата
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	*/
	public function offset($offset){
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Выставляет порядок сортировки
	 *
	 * @param $params array
	 *
	 * @example array('id') returned ORDER BY `id` ASC
	 * @example array('id' => 'desc') returned ORDER BY `id` DESC
	 * @example array('id' => 'desc', 'name' => 'ASC') returned ORDER BY `id` DESC, `name` ASC
	 * @example array('id' => 'desc', 'name') returned ORDER BY `id` DESC, `name` ASC
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function order($params){
		if(!is_array($params) && !is_string($params)){
			throw new MySQLSelectException("order params must be array or string");
		}

		$this->order = $params;

		return $this;
	}

	/**
	 * Выставляет поля для выборки. Повторное использование в одном экземпляре Select объединяет колонки
	 *
	 * @param $columns array
	 *
	 * @example ['`example`', '`t`.`test`'] returned SELECT `example`, `t`.`test`
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new MySQLSelectException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLSelectException('columns must be array');
		}

		$this->columns = array_replace_recursive($this->columns, $columns);

		return $this;
	}

	/**
	 * Группирует результаты запроса по выбранным колонкам
	 *
	 * @param $columns array
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function group($columns){

		if(empty($columns)){
			throw new MySQLSelectException('group must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLSelectException('group must be array');
		}

		$this->group = array_replace_recursive($this->group, $columns);

		return $this;
	}

	/**
	 * Создает вхождение в таблицу используя оператор LEFT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function leftjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('left', $table, $alias, $columns, $values, $type);
	}

	/**
	 * Создает вхождение в таблицу используя оператор RIGHT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function rightjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('right', $table, $alias, $columns, $values, $type);
	}

	/**
	 * Создает вхождение в таблицу используя оператор INNER JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLSelectException
	 *
	 * @return \Alonity\Components\Database\MySQL\Select()
	 */
	public function innerjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('inner', $table, $alias, $columns, $values, $type);
	}

	private function _join($jointype='', $table, $alias='', $columns=[], $values=[], $type=0x538){

		if(!empty($jointype) && !in_array($jointype, $this->jointypes)){
			throw new MySQLSelectException("unexpected jointype");
		}

		if(empty($table) || !is_string($table)){
			throw new MySQLSelectException("join param table must be a string");
		}

		if(!is_string($alias)){
			throw new MySQLSelectException("join param alias must be a string");
		}

		if(empty($columns)){
			throw new MySQLSelectException("join param columns must be not empty");
		}

		if(!is_array($columns)){
			throw new MySQLSelectException("join param columns must be array");
		}

		if(!is_array($values)){
			throw new MySQLSelectException("join param values must be array");
		}

		$this->join[] = [
			'jointype' => $jointype,
			'table' => $table,
			'alias' => (is_string($alias) && !empty($alias)) ? $alias : null,
			'columns' => $columns,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	private function filterJoin($join){
		$result = "";

		if(empty($join)){
			return $result;
		}

		foreach($join as $ar){
			if(!empty($ar['jointype']) && !in_array($ar['jointype'], $this->jointypes)){
				throw new MySQLSelectException("unexpected jointype");
			}

			if(empty($ar['table']) || !is_string($ar['table'])){
				throw new MySQLSelectException("join param table must be a string");
			}

			if(!is_string($ar['alias'])){
				throw new MySQLSelectException("join param alias must be a string");
			}

			if(!isset($ar['columns']) || empty($ar['columns'])){
				throw new MySQLSelectException("join param columns must be not empty");
			}

			if(!is_array($ar['columns'])){
				throw new MySQLSelectException("join param columns must be array");
			}

			if(!is_array($ar['values'])){
				throw new MySQLSelectException("join param values must be array");
			}

			$jointype = strtoupper($ar['jointype']);

			$as = (is_null($ar['alias'])) ? "" : "AS `{$ar['alias']}`";

			$result .= "$jointype JOIN `{$ar['table']}` $as ON ";

			if($ar['type']==self::WHERE_OR){
				$result .= implode(' OR ', $ar['columns']);
			}else{
				$result .= implode(' AND ', $ar['columns']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLSelectException("params columns and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = @mysql_escape_string($value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return $result;
	}

	private function filterGroup($columns){

		if(empty($columns)){ return ""; }

		if(!is_array($columns)){
			throw new MySQLSelectException("group must be array");
		}

		return 'GROUP BY '.implode(', ', $columns);
	}

	private function filterFrom($from){
		if(empty($from)){
			return '';
		}

		if(is_array($from)){
			$as = key($from);

			if(sizeof($from)!=1 || !is_string($as)){
				throw new MySQLSelectException("from array key must be a string");
			}

			$result = "FROM `{$from[$as]}` AS `$as`";
		}elseif(is_string($from)){
			$result = "FROM `{$from}`";
		}else{
			throw new MySQLSelectException("from must be array or string");
		}

		return $result;
	}

	private function filterColumns($columns){

		if(empty($columns)){ return "*"; }

		if(!is_array($columns)){
			throw new MySQLSelectException("columns must be array");
		}

		$items = [];

		foreach($columns as $k => $v){
			if(is_int($k)){
				$items[] = "$v";
			}else{
				$items[] = "$k AS $v";
			}
		}

		return implode(', ', $items);
	}

	private function filterWhere($where){
		$result = "";

		if(empty($where)){ return $result; }

		if(empty($where)){
			throw new MySQLSelectException("where must be not empty");
		}

		if(!is_array($where)){
			throw new MySQLSelectException("where must be array");
		}

		foreach($where as $ar){
			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLSelectException("params where and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = @mysql_escape_string($value);

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

	private function filterHaving($having){
		if(empty($having)){
			return "";
		}

		return "HAVING $having";
	}

	private function filterOrder($order){

		$result = "";

		if(empty($order)){
			return $result;
		}

		if(!is_array($order) && !is_string($order)){
			throw new MySQLSelectException("order must be array or string");
		}

		if(is_string($order)){
			$result = $order;
		}else{
			foreach($order as $k => $v){

				if(!is_array($v)){
					$by = (strtolower($v)!='desc') ? 'ASC' : 'DESC';

					$result .= "$k $by,";
				}else{
					$by = (strtolower($v[0])!='desc') ? 'ASC' : 'DESC';

					unset($v[0]);

					$v = array_map(function($value){ return "'$value'"; }, $v);

					$props = implode(", ", $v);

					$result .= "FIELD($k, $props) $by,";
				}
			}

			$result = mb_substr($result, 0, -1, 'UTF-8');
		}

		return "ORDER BY $result";
	}

	public function getSQL(){

		if(!is_null($this->sql)){
			return $this->sql;
		}

		$this->sql = $this->compileSQL();

		return $this->sql;
	}

	private function compileSQL(){
		$columns = $this->filterColumns($this->columns);

		$from = $this->filterFrom($this->from);

		$join = $this->filterJoin($this->join);

		$where = $this->filterWhere($this->where);

		$group = $this->filterGroup($this->group);

		$having = $this->filterHaving($this->having);

		$order = $this->filterOrder($this->order);

		$limit = $this->filterLimit($this->limit);

		$offset = $this->filterOffset($this->offset);

		return "SELECT $columns $from $join $where $group $having $order $limit $offset";
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
			return false;
		}

		return true;
	}

	public function getError(){
		return mysql_error($this->obj);
	}

	public function getArray(){
		$result = [];

		if(is_null($this->result) || $this->result===false){
			return $result;
		}

		while($ar = mysql_fetch_row($this->result)){
			$result[] = $ar;
		}

		return $result;
	}

	public function getAssoc(){
		$result = [];

		if(is_null($this->result) || $this->result===false){
			return $result;
		}

		while($ar = mysql_fetch_assoc($this->result)){
			$result[] = $ar;
		}

		return $result;
	}

	public function getNum(){
		if(is_null($this->result) || $this->result===false){
			return 0;
		}

		return mysql_num_rows($this->result);
	}
}

?>